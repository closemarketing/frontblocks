const gulp = require('gulp'); 
const minify = require('gulp-minify');
const cleanCSS = require('gulp-clean-css');
 
gulp.task('compress', async function() {
  gulp.src(['./assets/animations/*.js'])
    .pipe(minify())
    .pipe(gulp.dest('./assets/dist'));
	gulp.src(['./assets/carousel/*.js'])
		.pipe(minify())
		.pipe(gulp.dest('./assets/dist'));
	gulp.src(['./assets/sticky-column/*.js'])
    .pipe(minify())
    .pipe(gulp.dest('./assets/dist'));    
		gulp.src(['./assets/gallery/*.js'])
    .pipe(minify())
    .pipe(gulp.dest('./assets/dist'));
	gulp.src(['./assets/post/*.js'])
    .pipe(minify())
    .pipe(gulp.dest('./assets/dist'));
	gulp.src('./assets/animations/*.css')
    .pipe(cleanCSS({compatibility: 'ie8'}))
    .pipe(gulp.dest('./assets/dist'));
	gulp.src('./assets/carousel/*.css')
		.pipe(cleanCSS({compatibility: 'ie8'}))
		.pipe(gulp.dest('./assets/dist'));
	gulp.src('./assets/sticky-column/*.css')
    .pipe(cleanCSS({compatibility: 'ie8'}))
    .pipe(gulp.dest('./assets/dist'));
	gulp.src('./assets/gallery/*.css')
    .pipe(cleanCSS({compatibility: 'ie8'}))
    .pipe(gulp.dest('./assets/dist'));
	gulp.src('./assets/post/*.css')
    .pipe(cleanCSS({compatibility: 'ie8'}))
    .pipe(gulp.dest('./assets/dist'));
});