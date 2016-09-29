
$(document).ready(function(){

    var $listeBillets = $('.ticket-fill');

    var index = $listeBillets.length;

    var addButton = $('<button id="add-ticket" class="btn btn-default">Ajouter un billet</button>');

    $('.resa-submit:first').before(addButton);

    addButton.click(function(e){
        e.preventDefault();
        addTicket(index);
    });

    for(var i = 0 ; i < index ; i++){
        $listeBillets[i].id = i+1;
        $listeBillets[i].querySelector('.ticket-nb').textContent = i+1 ;
    }

    if(index > 1){
        for (var j = 2 ; j <= index ; j++){
            addSupressButton($('#' + j));
        }
    }

    function addTicket(){

        var modele = $('#proto-container').attr('data-prototype')
                .replace(/__name__/g, index)
            ;

        var $proto = $(modele);

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