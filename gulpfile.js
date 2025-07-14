const gulp = require('gulp'); 
const minify = require('gulp-minify');
const cleanCSS = require('gulp-clean-css');
 
gulp.task('compress', async function() {
  gulp.src(['./includes/animations/*.js'])
    .pipe(minify())
    .pipe(gulp.dest('./includes/dist'));
	gulp.src(['./includes/carousel/*.js'])
		.pipe(minify())
		.pipe(gulp.dest('./includes/dist'));
	gulp.src('./includes/animations/*.css')
    .pipe(cleanCSS({compatibility: 'ie8'}))
    .pipe(gulp.dest('./includes/dist'));
	gulp.src('./includes/carousel/*.css')
		.pipe(cleanCSS({compatibility: 'ie8'}))
		.pipe(gulp.dest('./includes/dist'));
});