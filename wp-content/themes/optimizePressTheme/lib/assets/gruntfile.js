module.exports = function(grunt) {
 
    // Project configuration.
    grunt.initConfig({
 
        //Read the package.json (optional)
        pkg: grunt.file.readJSON('package.json'),
 
        // Metadata.
        meta: {
            basePath: '../',
            srcPath: '../src/',
            deployPath: '../deploy/'
        },
 
        banner: '/*! <%= pkg.name %> - v<%= pkg.version %> - ' +
                '<%= grunt.template.today("yyyy-mm-dd") %>\n' +
                '* Copyright (c) <%= grunt.template.today("yyyy") %> ',
 
        // Task configuration.
        concat: {
            options: {
                stripBanners: true
            },
            dist: {
                src: ['mystyle.css', 'default.css'],
                dest: 'destino/resultado.css'
            }
        },
        cssmin: {
          compress: {
            files: {
              "destino/resultado.min.css": "destino/resultado.css"
            }
          }
        }
        });
   
 
    // These plugins provide necessary tasks.
    grunt.loadNpmTasks('grunt-contrib-concat');
 
    // Default task
    grunt.registerTask('default', ['concat']);
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-jshint');
};