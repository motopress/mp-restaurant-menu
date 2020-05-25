const gulp = require('gulp'),
    less = require('gulp-less'),
    plumber = require('gulp-plumber'),
    autoprefixer = require('gulp-autoprefixer'),
    notifier = require('gulp-notify'),
	cssmin = require('gulp-cssmin');
	var rename = require('gulp-rename');


gulp.task('less', function () {
   return gulp.src(['./media/less/admin-styles.less', './media/less/style.less'])
       .pipe(plumber({
           errorHandler: notifier.onError("Error: <%= error.messageOriginal %>")
       }))
       .pipe(less())
       .pipe(autoprefixer())
	   .pipe(gulp.dest('./media/css'))
	   .pipe(cssmin())
	   .pipe(rename({ extname: '.min.css' }))
       .pipe(gulp.dest('./media/css'))
});

gulp.task('default', gulp.series( function (done) {
    gulp.watch(['./media/less/admin-styles.less', './media/less/style.less'], gulp.series('less'));
    done();
}));