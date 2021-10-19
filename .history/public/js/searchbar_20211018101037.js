function search_sorties() {
    let searchbarValue = document.getElementById('searchbar').value.toLowerCase();
    let sorties_nom = document.getElementsByClassName('sortie_nom');
    let sorties = document.getElementsByClassName('sortie');
    let isEmpty = true;



    // Si aucun résultat trouvé
    if (isEmpty(sorties_nom)) {
        deleteTable();
    } else {
        // On affiche le tableau si il a était enlevé
        tab_sorties.style.display = "";
        for (i = 0; i < sorties_nom.length; i++) {
            if (!sorties_nom[i].innerHTML.toLowerCase().includes(searchbarValue)) {
                // On cache la sortie
                sorties_nom[i].parentNode.style.display = "none";
            } else {
                // On laisse afficher la sortie
                sorties_nom[i].parentNode.style.display = "";

            }
        }
    }
}

function deleteTable() {
    // on cache le tableau
    let tab_sorties = document.getElementById('tab_sorties');
    tab_sorties.style.display = "none";
    // on le remplace par un "aucune résultat"
    let not_found = document.createElement
}

function isEmpty(sorties_nom) {
    isEmpty = true;

    // Vérification si le tableau est vide
    for (let sortie of sorties_nom) {
        if (sortie.parentNode.style.display != "none") {
            isEmpty = false
        }
    }

    return isEmpty;
}