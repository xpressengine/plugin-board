var gulp = require('gulp');
var react = require('gulp-react');

gulp.task('default', function() {
    return gulp.src('./assets/js/*.jsx')
        .pipe(react())
        .pipe(gulp.dest('./assets/js'))
});