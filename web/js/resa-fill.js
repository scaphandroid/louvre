
$(document).ready(function(){

    var index = $('.ticket-fill').length;

    $addButton = $('<button id="add-ticket" class="btn btn-default">Ajouter un billet</button>');

    $('#resa-submit').before($addButton);

    $addButton.click(function(e){
        e.preventDefault();
        addTicket(index);
    });

    if(index > 1){
        for (var i = 2 ; i <= index ; i++){
            addSupressButton($('#' + i));
        }
    }

    function addTicket(){

        //TODO UNDIQUEMENT POUR L'AFFICHAGE POUR LE MOMENT ET NE MARCHERA PAS SI PAS DE TICKET AU DEPART
        var $proto = $('.ticket-fill:first').clone();
        $proto.attr('id', index+1);
        $proto.find('.ticket-nb').text(index+1);
        //suppression du bouton supprimé éventuellement hérité
        $proto.find('.ticket-delete').remove();
        addSupressButton($proto);
        $('.ticket-fill:last').after($proto);
        //si on avait avant 1 seul billet, ajout d'un bouton pour pouvoir supprimer le premier billet
        if(index == 1){
            addSupressButton($('.ticket-fill:first'));
        }
        index ++;
    }

    function addSupressButton($element){

        var $deleteLink = $('<a href="#" class="btn btn-danger ticket-delete">Supprimer ce billet</a>');

        $element.append($deleteLink);

        $deleteLink.click(function(e){
            e.preventDefault();
            $element.remove();
            index--;
            //TODO mise à jour des index des tickets restants !!
            //si il ne reste plus qu'un seul billet, on supprime son bouton supprimer
            if(index == 1){
                $('.ticket-fill').find('.ticket-delete').remove();
            }
            return false;
        })
    }
});