var gulp = require('gulp');
var react = require('gulp-react');
var babel = require('gulp-babel');

gulp.task('default', function() {
    return gulp.src('./assets/js/*.jsx')
        .pipe(react({es6module: true}))
        .pipe(babel())
        .pipe(gulp.dest('./assets/js'))
});