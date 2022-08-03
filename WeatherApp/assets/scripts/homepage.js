import '../styles/homepage.css';
import '../bootstrap';



function setCookie(name, value, days) {
    let date = new Date();
    date.setTime(date.getTime() + (days*1000*60*60*24));
    document.cookie = `${name}=${value};expires=${date.toUTCString()};path=/`;
}
function getCityById(id, del=false) {
    for(let city of cities) {
        if (city['id'] == id) return city;
    }
}
function updateUserCity(id, hasChange=true) {
    if (hasChange) {
        $(`#${userCity['id']}.city`).css('background-color', 'initial');
        userCity = getCityById(id);
    }
    $('.userCity').html(`${userCity[locale] ?? userCity['name']}`);
    $('.userCityTemp').html(`${userCity['temp_c']} °C`);
    $('.userCityIcon').attr("src",`images/${userCity.icon}.png`);
    if (hasChange) $(`#${userCity['id']}.city`).css('background-color', '#4F805D');
}
function citiesAddOne(city) {
    cities.push(city);
    var row = $(`<li class="city" id="${city['id']}">${city[locale] ?? city['name']}</li>`);
    $('.cities').append(row); 
}
function citiesDeleteOne(id, liElement) {
    cities.forEach(function(city, index, object) {
        if (city['id'] === id) {
            if (del) object.splice(index, 1);
        }
    });
    liElement.remove();
}

$(document).ready(function() {
    // Odświerzaj pogodę co 10 minut
    const updateWeather = setInterval(() => {
        $.ajax({
            url: '/update',
            method: 'POST',
            data: {'refresh': 1},
            success: function(data, status) {
                cities = data;
                updateUserCity(userCity['id'], false);
            },
        });
    }, 600000);
    
    //Zmiana rozmiaru okna
    let smallWindow = $(window).width() <= 600 ? true : false;
    $(window).on('resize', function(event) {
        if (smallWindow && $(window).width() > 600) {
            $('.container_icon').show();
            $('.menuPNG').hide();
            $('.container_userCity').show();
            smallWindow = false;
        }
        else if (!smallWindow && $(window).width() <= 600) {
            $('.container_icon').hide();
            $('.menuPNG').show();
            smallWindow = true;
        }
    })

    // Wyświetlanie miast
    $(document).click(function(event) {
        if ($(event.target).is('.searchCityInput')) return;
        else if ($(event.target).is('.city') && event.shiftKey && event.ctrlKey) return;
        else if ($(event.target).is('.cityPNG')) {
            if ($('.cities').is(":visible")) $('.cities').hide();
            else $('.cities').show();
            if ($('.languages').is(":visible")) $('.languages').hide();
            if ($('.searchedCities').is(":visible")) $('.searchedCities').hide();
        }
        else if ($(event.target).is('.languagesPNG')) {
            if ($('.languages').is(":visible")) $('.languages').hide();
            else $('.languages').show();
            if ($('.cities').is(":visible")) $('.cities').hide();
            if ($('.searchedCities').is(":visible")) $('.searchedCities').hide();
        }
        else if ($(event.target).is('.searchCityPNG')) {
            if ($('.searchedCities').is(":visible")) $('.searchedCities').hide();
            else {
                $('.searchedCities').show();
                $('.searchCityInput').focus();
            }
            if ($('.cities').is(":visible")) $('.cities').hide();
            if ($('.languages').is(":visible")) $('.languages').hide();
        }
        else if ($(event.target).is('.menuPNG')) {
            $('.container_icon').show();
            $('.menuPNG').hide();
            $('.container_userCity').hide();
        }
        else {
            if ($('.cities').is(":visible")) $('.cities').hide();
            else if ($('.languages').is(":visible")) $('.languages').hide();
            else if ($('.searchedCities').is(":visible")) $('.searchedCities').hide();
            else if ($('.menuPNG').is(":hidden") && smallWindow) {
                $('.container_icon').hide();
                $('.menuPNG').show();
                $('.container_userCity').show();
            }
        }
    });

    // Zmiana wyświetlanego miasta
    $('.cities').on('click', '.city', function(event) {
        if ($(this).attr('id') == userCity['id']) return;
        let del = false;
        if (event.shiftKey && event.ctrlKey) del = true;
        else {
            updateUserCity($(this).attr('id'));
            if (user === false) setCookie('userCity', userCity['id'], 1);
        }
        $.ajax({
            url: '/update',
            method: 'POST',
            data: {'userCity': JSON.stringify({'city': $(this).attr('id'), 'delete': del})},
        });
        if (del) citiesDeleteOne($(this).attr('id'), $(this));
    });

    let startX = null;
    $('.cities').on('touchstart', '.city', function(event) {
        if ($(this).attr('id') == userCity['id']) return;
        let touchObject = event.changedTouches[0];
        startX = touchObject.pageX;
    });
    $('.cities').on('touchend', '.city', function(event) {
        let touchObject = event.changedTouches[0];
        if (startX !== null && startX - touchObject.pageX > 200) {
            $.ajax({
                url: '/update',
                method: 'POST',
                data: {'userCity': JSON.stringify({'city': $(this).attr('id'), 'delete': true})},
            });
            citiesDeleteOne($(this).attr('id'), $(this));
            startX = null;
        }
    });

    // Szukaj miasta
    let searchedCities = [];
    let currentRequest = null;
    $('.searchCityInput').off('keyup').on('keyup', function(event) {
        let searchedCity = $(this).val().trim();
        if (!searchedCity) $('.searchedCitiesBody').html('');
        else {
            currentRequest = $.ajax({
                url: '/update',
                method: 'POST',
                data: {'searchedCity': searchedCity},
                beforeSend : function() {           
                    if (currentRequest !== null) currentRequest.abort();
                },
                success: function(data, status) {
                    searchedCities = data;
                    $('.searchedCitiesBody').html('');
                    for (let [key, city] of Object.entries(data)) {
                        var row = $(`<tr class="searchedCity" id="${key}"><td id="name"></td><td id="country"></td></tr>`);
                        $('#name', row).html(city[locale] ?? city['name']);
                        $('#country', row).html(city['country']);
                        $('.searchedCitiesBody').append(row); 
                    }
                },
            });
        }
    });

    // Dodaj szukane miasto
    $('.searchedCitiesBody').on('click', '.searchedCity', function(event) {
        $('.searchedCitiesBody').html('');
        $('.searchCityInput').val('');
        $.ajax({
            url: '/update',
            method: 'POST',
            data: {'choosenCity': JSON.stringify(searchedCities[$(this).attr('id')])},
            success: function(data, status) {
                if (data['new']) citiesAddOne(data['city']);
                updateUserCity(data['city']['id']);
            }
        });
    });
});