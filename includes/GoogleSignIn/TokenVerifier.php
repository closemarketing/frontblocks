<?php
/**
 * Google ID token verification (RS256 / JWKS), no external dependencies.
 *
 * @package    FrontBlocks
 * @author     Closemarketing
 * @copyright  2025 Closemarketing
 * @version    1.0.0
 */

namespace FrontBlocks\GoogleSignIn;

defined( 'ABSPATH' ) || exit;

/**
 * Verifies Google-issued OpenID Connect ID tokens server-side.
 */
class TokenVerifier {

	/**
	 * Google's published JWKS endpoint.
	 *
	 * @var string
	 */
	const JWKS_URI = 'https://www.googleapis.com/oauth2/v3/certs';

	/**
	 * Transient key used to cache the fetched JWKS.
	 *
	 * @var string
	 */
	const JWKS_TRANSIENT = 'frbl_google_jwks';

	/**
	 * Valid Google issuers.
	 *
	 * @var string[]
	 */
	const VALID_ISSUERS = array( 'https://accounts.google.com', 'accounts.google.com' );

	/**
	 * Verify a Google ID token and return its payload if valid.
	 *
	 * Checks signature (RS256 against Google's JWKS), issuer, audience
	 * (client ID), expiry and email_verified before trusting any claim.
	 *
	 * @param string $id_token  Raw JWT ID token from Google Identity Services.
	 * @param string $client_id Expected OAuth client ID (audience).
	 * @return array|\WP_Error Decoded payload on success, WP_Error otherwise.
	 */
	public static function verify( $id_token, $client_id ) {
		if ( ! is_string( $id_token ) || '' === $id_token ) {
			return new \WP_Error( 'frbl_gsi_missing_token', __( 'Missing Google credential.', 'frontblocks' ) );
		}

		$parts = explode( '.', $id_token );
		if ( 3 !== count( $parts ) ) {
			return new \WP_Error( 'frbl_gsi_malformed_token', __( 'Malformed Google credential.', 'frontblocks' ) );
		}

		list( $header_b64, $payload_b64, $signature_b64 ) = $parts;

		$header  = json_decode( self::base64url_decode( $header_b64 ), true );
		$payload = json_decode( self::base64url_decode( $payload_b64 ), true );

		if ( ! is_array( $header ) || ! is_array( $payload ) ) {
			return new \WP_Error( 'frbl_gsi_malformed_token', __( 'Malformed Google credential.', 'frontblocks' ) );
		}

		if ( 'RS256' !== ( $header['alg'] ?? '' ) ) {
			return new \WP_Error( 'frbl_gsi_unsupported_alg', __( 'Unsupported token signing algorithm.', 'frontblocks' ) );
		}

		$kid = $header['kid'] ?? '';
		$jwk = self::get_jwk( $kid );

		if ( ! $jwk ) {
			return new \WP_Error( 'frbl_gsi_unknown_key', __( 'Could not verify Google credential signature.', 'frontblocks' ) );
		}

		$pem = self::jwk_to_pem( $jwk );
		if ( ! $pem ) {
			return new \WP_Error( 'frbl_gsi_key_error', __( 'Could not process Google signing key.', 'frontblocks' ) );
		}

		$signing_input = $header_b64 . '.' . $payload_b64;
		$signature     = self::base64url_decode( $signature_b64 );

		$valid = openssl_verify( $signing_input, $signature, $pem, OPENSSL_ALGO_SHA256 );

		if ( 1 !== $valid ) {
			return new \WP_Error( 'frbl_gsi_bad_signature', __( 'Google credential signature is invalid.', 'frontblocks' ) );
		}

		// Issuer.
		if ( ! in_array( $payload['iss'] ?? '', self::VALID_ISSUERS, true ) ) {
			return new \WP_Error( 'frbl_gsi_bad_issuer', __( 'Google credential has an unexpected issuer.', 'frontblocks' ) );
		}

		// Audience must match our configured Client ID.
		if ( ( $payload['aud'] ?? '' ) !== $client_id ) {
			return new \WP_Error( 'frbl_gsi_bad_audience', __( 'Google credential was not issued for this site.', 'frontblocks' ) );
		}

		// Expiry.
		if ( empty( $payload['exp'] ) || (int) $payload['exp'] < time() ) {
			return new \WP_Error( 'frbl_gsi_expired', __( 'Google credential has expired. Please try signing in again.', 'frontblocks' ) );
		}

		// Only accept verified email addresses.
		$email_verified = $payload['email_verified'] ?? false;
		if ( true !== $email_verified && 'true' !== $email_verified ) {
			return new \WP_Error( 'frbl_gsi_email_unverified', __( 'Your Google account email is not verified.', 'frontblocks' ) );
		}

		if ( empty( $payload['email'] ) || ! is_email( $payload['email'] ) || empty( $payload['sub'] ) ) {
			return new \WP_Error( 'frbl_gsi_incomplete_payload', __( 'Google credential is missing required information.', 'frontblocks' ) );
		}

		return $payload;
	}

	/**
	 * Get the JWK matching a key ID, fetching/caching Google's JWKS as needed.
	 *
	 * @param string $kid Key ID from the token header.
	 * @return array|null
	 */
	private static function get_jwk( $kid ) {
		if ( '' === $kid ) {
			return null;
		}

		$keys = get_transient( self::JWKS_TRANSIENT );

		if ( ! is_array( $keys ) || ! self::find_key( $keys, $kid ) ) {
			$keys = self::fetch_jwks();
			if ( is_array( $keys ) ) {
				set_transient( self::JWKS_TRANSIENT, $keys, HOUR_IN_SECONDS );
			}
		}

		return is_array( $keys ) ? self::find_key( $keys, $kid ) : null;
	}

	/**
	 * Find a key by ID within a JWKS key set.
	 *
	 * @param array  $keys JWKS keys array.
	 * @param string $kid  Key ID to find.
	 * @return array|null
	 */
	private static function find_key( $keys, $kid ) {
		foreach ( $keys as $key ) {
			if ( isset( $key['kid'] ) && $key['kid'] === $kid ) {
				return $key;
			}
		}
		return null;
	}

	/**
	 * Fetch Google's current JWKS.
	 *
	 * @return array|null List of JWK entries, or null on failure.
	 */
	private static function fetch_jwks() {
		$response = wp_remote_get( self::JWKS_URI, array( 'timeout' => 10 ) );

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return null;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		return isset( $body['keys'] ) && is_array( $body['keys'] ) ? $body['keys'] : null;
	}

	/**
	 * Convert an RSA JWK (n, e) into a PEM-encoded public key.
	 *
	 * @param array $jwk JWK entry.
	 * @return string|null PEM public key, or null on failure.
	 */
	private static function jwk_to_pem( $jwk ) {
		if ( empty( $jwk['n'] ) || empty( $jwk['e'] ) ) {
			return null;
		}

		$modulus  = self::der_integer( self::base64url_decode( $jwk['n'] ) );
		$exponent = self::der_integer( self::base64url_decode( $jwk['e'] ) );

		$rsa_public_key = self::der_sequence( $modulus . $exponent );

		// Standard DER prefix for the rsaEncryption AlgorithmIdentifier.
		$algorithm_id = "\x30\x0d\x06\x09\x2a\x86\x48\x86\xf7\x0d\x01\x01\x01\x05\x00";

		$bit_string = "\x03" . self::der_length( strlen( $rsa_public_key ) + 1 ) . "\x00" . $rsa_public_key;

		$spki = self::der_sequence( $algorithm_id . $bit_string );

		return "-----BEGIN PUBLIC KEY-----\n" . chunk_split( base64_encode( $spki ), 64, "\n" ) . "-----END PUBLIC KEY-----\n"; // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- standard PEM encoding, not obfuscation.
	}

	/**
	 * DER-encode a length value.
	 *
	 * @param int $length Length to encode.
	 * @return string
	 */
	private static function der_length( $length ) {
		if ( $length < 128 ) {
			return chr( $length );
		}

		$bytes = '';
		while ( $length > 0 ) {
			$bytes  = chr( $length & 0xff ) . $bytes;
			$length = $length >> 8;
		}

		return chr( 0x80 | strlen( $bytes ) ) . $bytes;
	}

	/**
	 * DER-encode an unsigned integer (RSA modulus/exponent).
	 *
	 * @param string $bytes Raw big-endian integer bytes.
	 * @return string
	 */
	private static function der_integer( $bytes ) {
		$bytes = ltrim( $bytes, "\x00" );
		if ( '' === $bytes ) {
			$bytes = "\x00";
		}
		if ( ord( $bytes[0] ) > 0x7f ) {
			$bytes = "\x00" . $bytes;
		}

		return "\x02" . self::der_length( strlen( $bytes ) ) . $bytes;
	}

	/**
	 * DER-encode a SEQUENCE.
	 *
	 * @param string $content Raw sequence content.
	 * @return string
	 */
	private static function der_sequence( $content ) {
		return "\x30" . self::der_length( strlen( $content ) ) . $content;
	}

	/**
	 * Decode a base64url string.
	 *
	 * @param string $data Base64url-encoded data.
	 * @return string
	 */
	private static function base64url_decode( $data ) {
		$data = str_replace( array( '-', '_' ), array( '+', '/' ), $data );
		$pad  = strlen( $data ) % 4;
		if ( $pad ) {
			$data .= str_repeat( '=', 4 - $pad );
		}

		return (string) base64_decode( $data ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
	}
}
