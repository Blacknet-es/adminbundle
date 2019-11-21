var gulp = require('gulp');
var less = require('gulp-less');
var util = require('gulp-util');
var minifyCSS = require('gulp-csso');
var uglify = require('gulp-uglify');
var concat = require('gulp-concat');


var adminJS = [
    './web/bundles/trendixadmin/vendors/sparklines/jquery.sparkline.min.js',
    './web/bundles/trendixadmin/vendors/bootstrap-growl/bootstrap-growl.min.js',
    './web/bundles/trendixadmin/vendors/summernote/dist/summernote-updated.min.js',
    './web/bundles/trendixadmin/js/charts.js',
    './web/bundles/trendixadmin/js/functions.js',
    './web/bundles/trendixadmin/pickadate/lib/picker.js',
    './web/bundles/trendixadmin/pickadate/lib/picker.date.js',
    './web/bundles/trendixadmin/pickadate/lib/picker.time.js',
    './web/bundles/trendixadmin/js/datepicker.js',
    './web/bundles/trendixadmin/js/jscolor.min.js',
    './web/bundles/trendixadmin/js/image_uploader.js',
    './web/bundles/trendixadmin/js/conditional_fields.js',
    './web/bundles/trendixadmin/js/actions.js',
    './web/bundles/trendixadmin/jcrop/js/jquery.color.js',
    './web/bundles/trendixadmin/jcrop/js/jquery.fileupload.js',
    './web/bundles/trendixadmin/jcrop/js/jquery.Jcrop.min.js',
    './web/bundles/trendixadmin/jcrop/js/imageWidget.js'
];

var bowerDependencies = [
    './bower_components/flot/jquery.flot.js',
    './bower_components/flot/jquery.flot.resize.js',
    './bower_components/flot.curvedLines/curvedLines.js',
    './bower_components/jquery.easy-pie-chart/dist/jquery.easypiechart.min.js',
    './bower_components/moment/min/moment.min.js',
    './bower_components/fullcalendar/dist/fullcalendar.min.js',
    './bower_components/simpleWeather/jquery.simpleWeather.min.js',
    './bower_components/Waves/dist/waves.min.js',
    './bower_components/bootstrap-sweetalert/lib/sweet-alert.min.js',
    './bower_components/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.concat.min.js'
];

var appJS = [];

var headerJS = [];

function compileLess() {
    // You can change these regular expressions if you want to split admin and front stylesheets in different files.
    gulp.src([
        './web/bundles/*/**/app.less',
        './web/bundles/*/**/main.less',
    ])
        .pipe(less().on('error', util.log))
        .pipe(concat('app.css'))
        .pipe(minifyCSS())
        .pipe(gulp.dest('./web/css'));
    gulp.src([
        './web/bundles/trendixadmin/fonts/*',
        './web/bundles/trendixadmin/fonts/**/*'
    ])
        .pipe(gulp.dest('./web/fonts'));
    // If you want to build any more CSS/LESS resources in a different file, write in here
}

gulp.task('less', function() {
    compileLess();
});

function compileJS() {
    gulp.src(headerJS.concat([
        './bower_components/jquery/dist/jquery.js',
        './bower_components/bootstrap/dist/js/bootstrap.js'
    ]))
        .pipe(concat('header.js'))
        .pipe(uglify())
        .pipe(gulp.dest('./web/js'));

    gulp.src(bowerDependencies.concat(adminJS))
        .pipe(concat('footer.js'))
        .pipe(uglify())
        .pipe(gulp.dest('./web/js'));

    if(appJS.length) {
        gulp.src(appJS)
            .pipe(concat('app.js'))
            .pipe(uglify())
            .pipe(gulp.dest('./web/js'));
    }
}
gulp.task('js', function() {
    compileJS();
});

gulp.task('watch', function () {
    var onChange = function (event) {
        console.log('File '+event.path+' has been '+event.type);
    };
    gulp.watch('./web/bundles/*/**/*.less', ['less'])
        .on('change', compileLess);
    gulp.watch('./web/bundles/*/**/*.js', ['js'])
        .on('change', compileJS);
});

gulp.task('default', ['less', 'js']);