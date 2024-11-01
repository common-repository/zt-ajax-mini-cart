"use strict";

var gulp = require('gulp'),
    browserSync = require('browser-sync').create(),
    autoprefixer = require('gulp-autoprefixer'),
    csso = require('gulp-csso'),
    uglify = require('gulp-uglify'),
    rename = require('gulp-rename'),
    sass = require('gulp-sass');

// Static server
gulp.task('browser-sync', function() {
    var files = [
        'src/*.php',
        '*.php',
        'assets/css/*.css',
        'assets/sass/*.scss',
        'assets/js/*.js',
    ];

    browserSync.init(files, {
        proxy: 'http://localhost:8888/mailchimp-subscribe-for-wp.loc/',
        notify: false
    });
});

gulp.task('sass', function () {
    return gulp.src('assets/sass/**/*.scss')
        .pipe(sass().on('error', sass.logError))
        .pipe(autoprefixer({
            browsers: ['> 0%'],
            cascade: false
        }))
        .pipe(csso())
        .pipe(rename('style.min.css'))
        .pipe(gulp.dest('assets/css'))
        .pipe(browserSync.stream());
});

// Gulp task to minify JavaScript files
gulp.task('scripts', function() {
    return gulp.src('assets/js/main.js')
        .pipe(uglify())
        .pipe(rename('main.min.js'))
        .pipe(gulp.dest('assets/js'))
});

gulp.task('default', ['sass', 'scripts', 'browser-sync'], function () {
    gulp.watch('assets/sass/**/*.scss',['sass']);
    gulp.watch('assets/js/scripts.js', ['scripts']);
});
