var gulp       = require('gulp');
const uglify = require('gulp-uglify');
 

gulp.task('minify-js', function() {
    return gulp.src('pub/static/adminhtml/Magento/backend/**/*.min.js')
      .pipe(uglify())
      .pipe(gulp.dest('pub/static/adminhtml/Magento/backend/_site1')); 
  });