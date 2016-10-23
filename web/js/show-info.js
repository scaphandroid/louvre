/**
 * Created by alexis on 22/10/2016.
 */
$(document).ready(function(){

    var tarifActif = false;
    var infoActif = false;

    $('.show-resa').click(function (e){

        e.preventDefault();

        showAndHide('#content-main', '#hidden-tarifs', '#hidden-info');
    });


    $('.info-toggle').click(function(e){

        e.preventDefault();

        infoActif = !infoActif;

        showAndHide('#hidden-info', '#hidden-tarifs', '#content-main');
    });

    $('.tarifs-toggle').click(function(e){

        e.preventDefault();

        tarifActif = !tarifActif;

        showAndHide('#hidden-tarifs', '#hidden-info', '#content-main');
    });

    function showAndHide(el1, el2, el3){

        if($(el1).css('display') == 'none'){
            $(el1).show();
            $(el2).hide();
            $(el3).hide();
        }else{
            $('#hidden-info').hide();
            $('#hidden-tarifs').hide();
            $('#content-main').show();
        }
    }
});
