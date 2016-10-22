/**
 * Created by alexis on 22/10/2016.
 */
$(document).ready(function(){

    $('.info-toggle').click(function(e){

        e.preventDefault();

        $('#hidden-info').toggle();
        $('#content-main').toggle();
    });
});