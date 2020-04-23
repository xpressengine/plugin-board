const gulp = require('gulp')
const $ = require('gulp-load-plugins')()
const Path = require('path')
let generateSourceMaps = process.env.NODE_ENV !== 'production'

if (process.env.SOURCEMAPS === 'true' || process.env.SOURCEMAPS === '1') {
  generateSourceMaps = true
}

// scss 사용 할 때
const taskSass = function () {
  return gulp.src(['./src/scss/*.scss'])
    .pipe($.if(generateSourceMaps, $.sourcemaps.init()))
    .pipe($.plumber())
    .pipe($.sass({
      outputStyle: (generateSourceMaps) ? 'expanded' : 'compressed'
    }).on('error', $.sass.logError))
    .pipe($.autoprefixer())
    .pipe($.if(generateSourceMaps, $.sourcemaps.write('.')))
    .pipe(gulp.dest('./css'))
}
taskSass.displayName = 'sass'

const taskPosthtml = function () {
  const options = {
    root: '../markup/src'
  }

  const plugins = [
    require('posthtml-extend')(options),
    require('posthtml-include')(options)
  ]

  return gulp.src(['../markup/src/**/*.html', '!**/_*.html'])
    .pipe($.posthtml(plugins))
    .pipe(gulp.dest('../markup'))
}
taskPosthtml.displayName = 'posthtml'

const taskWatch = function () {
  gulp.watch(['./src/scss/*.scss'], gulp.series(taskSass))
  gulp.watch(['../markup/src/**/*.html'], gulp.series(taskPosthtml))
}

gulp.task('default', gulp.series(taskSass))
gulp.task('build', gulp.series(taskSass))
gulp.task('watch', taskWatch)

gulp.task(taskSass)
gulp.task(taskPosthtml)
