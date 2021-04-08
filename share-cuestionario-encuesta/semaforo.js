
var btn = document.getElementById('btn-semaforo');

var Semaforo = function (semaforo) {
    this.semaforo = semaforo;
    this.colores = {
        rojo: "rgb(255, 29, 29)",
        amarillo: "rgb(255, 235, 29)",
        verde: "rgb(35, 253, 0)"
    };

    this.tempRandom = function (min) {
        return Math.floor((Math.random() * min) + 1);
    };

    this.paint = function (elemento, randomValue) {
        var min = +this.semaforo.min, max = +this.semaforo.max;
        var medio = (min + max) / 2;

        if (randomValue > min && randomValue < medio) {
            elemento[2].style.background = this.colores.verde;
            elemento[1].style.background = '';
            elemento[0].style.background = '';

        } else {
            if (randomValue > medio && randomValue >= max) {
                elemento[0].style.background = this.colores.rojo;
                elemento[1].style.background = '';
                elemento[2].style.background = '';
            } else {
                if (min == max) {
                    alert("El mínimo y máximo son iguales");
                } else {
                    elemento[1].style.background = this.colores.amarillo;
                    elemento[0].style.background = '';
                    elemento[2].style.background = '';
                }
            }
        }
    };
};

function getTemperatura() {
    var sem = document.getElementsByTagName('input');
    console.log(sem[0].value);
    return {
        min: sem[0].value,
        max: sem[1].value,
        time: sem[2].value,
    };
};

btn.addEventListener('click', function () {
    var temp = getTemperatura();
    var sem = new Semaforo(temp);

    //ejemplo
    var div = document.getElementById('div-semaforo');
    var valorAleatorio = 3; // generar valor aleatorio.
    sem.paint(div, valorAleatorio, sem.semaforo.min, sem.semaforo.max);
});