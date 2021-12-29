"use strict";

/*--------------------------------------/
/	добавление и удаление полей для 	/ 
/	внесения IP-адресов источников и 	/
/	номеров сигнатур (раздел дежурного)	/
/					v0.1 26.08.2014		/
/--------------------------------------*/
//получаем путь к исполняемому скрипту
function getPath() {
  return window.location.href;
} //функция добавления IP-адресов источников


function addSrcIP() {
  var string = getPath();

  if (string.indexOf('addComputerImpact') != -1) {
    var nameSrcIp = document.getElementById('srcIp'); //клонируем форму с информацией по сигнатурам

    var cloneSrcIp = nameSrcIp.cloneNode(true); //создаем дополнительную форму

    var newNode = nameSrcIp.parentNode.insertBefore(cloneSrcIp, document.getElementById('exitId')); //очищаем атрибуты

    newNode.firstChild.nextSibling.firstChild.nextSibling.setAttribute('value', ''); //удаляем ненужный элемент DIV с информацией о ранее встречавшихся IP-адресах источниках

    if (newNode.lastChild.previousSibling.firstChild.nodeName == 'DIV') {
      var divInf = newNode.lastChild.previousSibling.firstChild;
      divInf.parentNode.removeChild(divInf);
    }
  } else {
    //выбираем главный элемент div с именем srcIp
    var srcIp = document.getElementById('srcIp'); //выбираем второго ребенка главного элемента div (первый ребенок является текстовой нодой)

    var divChild = srcIp.firstChild.nextSibling.childNodes; //создаем объект клонированных элементов 

    clonElements = {};

    for (var i = 0; i < divChild.length; i++) {
      //получаем элемент ipSrc и клонируем его
      if (divChild[i].nodeType == 1 && divChild[i].getAttribute('name') == 'ipSrc[]') {
        var cloneSrcIp = divChild[i].cloneNode(true); //очищаем атрибуты					

        cloneSrcIp.setAttribute('value', '');
      } //получаем элемент ipNum и клонируем его


      if (divChild[i].nodeType == 1 && divChild[i].getAttribute('name') == 'ipNum[]') {
        var cloneSrcIpNum = divChild[i].cloneNode(true); //очищаем атрибуты

        cloneSrcIpNum.setAttribute('value', '0');
      }
    } //формируем новые элементы


    var div = srcIp.appendChild(document.createElement('DIV'));
    div.appendChild(cloneSrcIp);
    var span = div.appendChild(document.createElement('SPAN'));
    span.appendChild(document.createTextNode(' (Страна) / '));
    div.appendChild(cloneSrcIpNum); //элемент '<br>'			

    div.appendChild(document.createElement('BR'));
  }
} //функция удаления IP-адресов источников


function delSrcIP() {
  var string = getPath();

  if (string.indexOf('addComputerImpact') != -1) {
    var srcIp = document.getElementsByName('srcIp');
    var delChild = '';

    for (var i = srcIp.length; i > 1; i--) {
      delChild = srcIp[i - 1];

      if (typeof delChild != 'undefined' && delChild.nodeType == 1 && delChild.nodeName == 'TR') {
        var srcId = document.getElementById('srcIp');
        var parent = srcId.parentNode; //удаляем элемент					

        parent.removeChild(delChild);
        break;
      }
    }
  } else {
    var srcIp = document.getElementById('srcIp');
    var delChild = '';

    for (var i = srcIp.childNodes.length; i > 1; i--) {
      delChild = srcIp.childNodes[i];

      if (typeof delChild != 'undefined' && delChild.nodeType == 1 && delChild.nodeName == 'DIV') {
        //удаляем элемент					
        srcIp.removeChild(delChild);
        break;
      }
    }
  }
} //функция для добавления сигнатур


function addSid() {
  var nameSid = document.getElementById('nameSid'); //клонируем форму с информацией по сигнатурам

  var cloneSid = nameSid.cloneNode(true); //создаем дополнительную форму

  var newNode = nameSid.parentNode.insertBefore(cloneSid, document.getElementById('stopId')); //очищаем форму с номером сигнатуры и ее количеством

  for (var a = 0; a < newNode.firstChild.nextSibling.childNodes.length; a++) {
    if (newNode.firstChild.nextSibling.childNodes[a].nodeType == 1) {
      newNode.firstChild.nextSibling.childNodes[a].setAttribute('value', '0');
    }
  }

  var string = getPath(); //очистить пояснения

  if (string.indexOf('addComputerImpact')) {
    newNode.firstChild.nextSibling.nextSibling.nextSibling.innerHTML = '';
  } else {
    newNode.firstChild.nextSibling.nextSibling.nextSibling.innerHTML = ' - описание не найдено';
  }
} //функция для удаления сигнатур


function delSid() {
  var nameSid = document.getElementById('nameSid');
  var trNode = nameSid.parentNode.childNodes; //получаем количество элементов с номером сигнатуры

  var a = document.getElementsByName('sid[]').length;

  for (var i = trNode.length; i > 1; i--) {
    if (typeof trNode[i] != 'undefined' && trNode[i].nodeType == 1 && trNode[i].nodeName == 'TR' && trNode[i].hasAttribute('id') && trNode[i].getAttribute('id') == 'nameSid') {
      //проверяем количество элементов с номером сигнатуры
      if (a == 1) {
        break;
      } //удаляем элемент с номером сигнатуры


      trNode[i].parentNode.removeChild(trNode[i]);
      break;
    }
  }
}