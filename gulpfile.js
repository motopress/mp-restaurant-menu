const gulp = require('gulp'),
    less = require('gulp-less'),
    plumber = require('gulp-plumber'),
    autoprefixer = require('gulp-autoprefixer'),
    notifier = require('gulp-notify'),
	cssmin = require('gulp-cssmin');


gulp.task('less', function () {
   return gulp.src(['./media/less/**/**/*.less', '!./media/less/no-js.less', '!./media/less/theme.less'])
       .pipe(plumber({
           errorHandler: notifier.onError("Error: <%= error.messageOriginal %>")
       }))
       .pipe(less())
       .pipe(autoprefixer())
	   .pipe(cssmin())
       .pipe(gulp.dest('./media/css'))
});

gulp.task('default', gulp.series( function (done) {
    gulp.watch('./media/less/**/**/*.less', gulp.series('less'));
    done();
}));