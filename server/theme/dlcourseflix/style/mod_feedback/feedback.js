document.addEventListener('DOMContentLoaded', function () {
    // Tu función aquí
    console.log('La página ha cargado completamente.');

    //const multichoice = document.getElementsByClassName('feedback-item-multichoice')
    const multichoice = document.querySelectorAll('.feedback-item-multichoice.multichoice-vertical .felement > span');
    const labelMultichoice = document.querySelectorAll('.feedback-item-multichoice.multichoice-vertical .felement > span > label');
    const multichoiceRated = document.querySelectorAll('.feedback-item-multichoicerated .felement > span');
    const labelMultichoiceRated = document.querySelectorAll('.feedback-item-multichoicerated .felement > span > label');
    const largeMultichoice = document.querySelectorAll('.feedback-item-multichoice.multichoice-horizontal div.felement > span');
    const largeMultichoiceLabel = document.querySelectorAll('.feedback-item-multichoice.multichoice-horizontal div.felement > span > label');

    var labelNames = [];
    var labelRatedNames = []
    var labelLargeNames = []

    if (labelMultichoice) {
        labelMultichoice.forEach(function (item) {
            // Guardar el label y setear a valor vacío
            labelNames.push(item.textContent)
            item.textContent = ''
        });
    }

    if (labelMultichoiceRated) {
        labelMultichoiceRated.forEach(function (item) {
            // Guardar el label y setear a valor vacío
            labelRatedNames.push(item.textContent)
            item.textContent = ''
        });
    }

    var reformat = false
    if (largeMultichoiceLabel) {
        largeMultichoiceLabel.forEach(function (item) {
            // Guardar el label y setear a valor vacío
            if (item.textContent.length > 2) {
                reformat = true;
            }
        });
    }

    cont = 0

    if (reformat) {
        largeMultichoiceLabel.forEach(function (item) {
            // Guardar el label y setear a valor vacío
            labelLargeNames.push(item.textContent)
            item.textContent = ''
        });

        largeMultichoice.forEach(function (item) {
            // Realiza alguna acción con cada elemento multichoice

            var newLabel = document.createElement('p');
            newLabel.textContent = labelLargeNames[cont]
            newLabel.style.whiteSpace = 'nowrap';
            newLabel.style.margin = '0';

            // Agregar el nuevo label al item
            item.appendChild(newLabel);
            cont++

            //console.log(item);
        });

        largeMultichoice[0].parentElement.parentElement.classList.remove('multichoice-horizontal')
        largeMultichoice[0].parentElement.parentElement.classList.add('multichoice-vertical')
    }

    var cont = 0

    if (multichoice) {
        multichoice.forEach(function (item) {
            // Realiza alguna acción con cada elemento multichoice

            var newLabel = document.createElement('p');
            newLabel.textContent = labelNames[cont]
            newLabel.style.whiteSpace = 'nowrap';
            newLabel.style.margin = '0';

            // Agregar el nuevo label al item
            item.appendChild(newLabel);
            cont++

            //console.log(item);
        });
    }

    var cont = 0

    if (multichoiceRated) {
        multichoiceRated.forEach(function (item) {
            // Realiza alguna acción con cada elemento multichoice

            var newLabel = document.createElement('p');
            newLabel.textContent = labelRatedNames[cont]
            newLabel.style.whiteSpace = 'nowrap';

            // Agregar el nuevo label al item
            item.appendChild(newLabel);
            cont++

            //console.log(item);
        });
    }

    document.getElementById('page').style.display = 'block'
});