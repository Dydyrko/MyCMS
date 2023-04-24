'use strict';

function post(url,f,params,type,method,progressBar,Header){	//Header=[["Cache-Control","no-cache"],[…]]
	var d1=Date.now();					//пример: function f(text,status,XML){alert(text+"\n"+status+"\n"+XML)}
	if(!method){method="POST"}
	if(method=="GET"){url+="=&"+params}
	if(!type || type!='formData'){
		type=(type=="xml"?"text/plain;charset=UTF-8":"application/x-www-form-urlencoded");	//text/xml;charset=UTF-8
	}
	if(!progressBar){progressBar=document.querySelector("PROGRESS")}
	var xmlHttp = false;
	if(window.ActiveXObject){
		xmlHttp=new ActiveXObject("Microsoft.XMLHTTP")
	}else if(window.XMLHttpRequest){
		xmlHttp=new XMLHttpRequest()
	}
	xmlHttp.open(method,url,true);
	if(type!='formData'){xmlHttp.setRequestHeader("Content-type", type);}
	if(Header){
		for(var i=0;i<Header.length;i++){
			xmlHttp.setRequestHeader(Header[i][0],Header[i][1]);
		}
	}
	xmlHttp.timeout = 600000;	//ms
	xmlHttp.ontimeout = function(e){console.log(e,"timeout =",xmlHttp.timeout)}

	xmlHttp.onreadystatechange = function(){
		if(xmlHttp.readyState == 4 && xmlHttp.status > 99){
			f(xmlHttp.responseText,xmlHttp.status,xmlHttp.responseXML);
			if(progressBar && progressBar.tagName=="PROGRESS"){progressBar.value=0;}
			var d=Date.now();console.log(d-d1);
		}else if(progressBar && progressBar.tagName=="PROGRESS"){progressBar.value=xmlHttp.readyState*1/4}
	}
	if(progressBar && progressBar.tagName=="PROGRESS"){
		xmlHttp.upload.onprogress = function (e) {
			var v=e.loaded/e.total;
			progressBar.value = v;
			progressBar.title=progressBar.textContent=parseInt(v*100)+"%";
		}
	}
	//xmlHttp.onprogress=
	xmlHttp.onload=function(e){var d2=Date.now();console.log((d2-d1)+' ms',e.loaded,params)}
	xmlHttp.send(params);
	return xmlHttp
};

function ajx(
	evt,
	p,	//имя в массиве POST
	data,	//текст, передаваемый на сервер в поле POST с именем p: «"123&t="+encodeURIComponent(t)»
	div,	//блок: куда помещать ответ сервера (txt) или/и используемый в js
	js,	//скрипт действия типа "alert(txt);div=div.parentNode" или функция типа function f(evt,txt,div,p){div.checked=(txt==1)}
		//или массив, где первый элемент — функция или скрипт для выполнения до помещения txt в div, второй — после.
		//Можно div сохранить в p и обнулить…
	b,	//=1 или =2: ответ сервера не помещать в div, при b=2 помещать в div GIF (или SVG) ожидания. При 3 ответ помещать, но не помещать картинку ожидания
	title	//заголовок текста ответа
	,modal,method
	){
	var wait='/setup/i/t.gif';
	if(!method){method="POST"}
	function f(txt){
		var p;	//pointer
		if(typeof(T)!="undefined"){clearTimeout(T)}	//если не показали GIF ожидания, то уже он и не нужен
		if(js && typeof(js)=='object'){	//js массив из кода для выполнения до помещения txt в div, и кода для выполнения после
			var A=js;
			if(typeof(A[0])=="function"){A[0](evt,txt,div,p)}else{eval(A[0])}
			js=A[1]
		}
		if(!b || b==3){
			if(typeof(div)=="object"){
				div.innerHTML=txt;
			}else if(txt!=''){
				div=showDiv(
					(title?"<h2>"+title+"</h2>":"")+txt+"<a class=close>&times;</a>",0,modal
				);
			}
		}
		if(js){
			if(typeof(js)=="function"){js(evt,txt,div,p)}else{eval(js)}
		}
	};
	function t(){div.innerHTML="<img class=ajxT src='"+wait+"'>"};	//картинка "ждите" (стиль ajx.css)
	if(div && !b || b==2){var T=window.setTimeout(t,100)}
	if(typeof(lang)=='string'){	//язык указан GET. Или для выполнения указывается папка, в index.php которой обрабатывается запрос "/?ajx"
		var s=(lang=="ru"?"/?ajx":"/"+lang+"/?ajx")
	}else{	
		var	s=location.pathname.substr(0,4);	//первые 4 символа URL после доменного имени
		s=(
			s.search(/\/[a-z]{2}\//)==-1?	//соответствие шаблону "две лат.буквы между слэшами" ("/ru/", /en/)
			"/?ajx"				//для основного языка
			:s+"?ajx"			//для других языков — типа "/en/?ajx"
		);
	}
	post(s,f,p+"="+data,0,method,document.getElementsByTagName("PROGRESS")[0]);
	return false
}
function ajxFormData(evt,form,js,div,b){	//event,форма,скрипт, функция или массив до [и после] вставки txt,куда txt, b=ответ сервера не помещать в div
	function f(txt){
		if(js && typeof(js)=='object'){	//js массив из кода для выполнения до помещения txt в div, и кода для выполнения после
			var A=js;
			if(typeof(A[0])=="function"){A[0](evt,txt,form,div)}else{eval(A[0])}
			js=A[1]		//undefined при отсутствии второго элемента массива
		}
		if(!b){			//нет запрета отображения ответа
			if(div){	//ответ поместить в указанный блок
				div.innerHTML=txt
			}else{		//или поместить в форму
				form.innerHTML=txt
			}
		}
		if(js){		//выполнить js-код или функцию
			if(typeof(js)=="function"){js(evt,txt,form,div)}else{eval(js)}
		}
		var e=form.parentNode;	//если результат в окно "ajxAlert", то центрировать
		if(e && e.tagName=="CENTER" && e.parentNode.className && e.parentNode.className=="ajxAlert"){
			var t=(document.body.clientWidth-e.offsetWidth)/2;
			e.style.width=e.offsetWidth+"px";
			if(t<0){t=0}
			e.style.left=t+"px";
		}
	}
	var formData = new FormData(form);
console.log('lang',typeof(lang));
	if(typeof(lang)=='string'){	//Для выполнения указывается "папка", в index.php которой обрабатывается запрос "/?ajx". Было время, когда язык указывался GET - отсюда имя переменной. Используется для аякс из "/api", "/parse"
		var s=(lang=="ru"?"/?ajx":"/"+lang+"/?ajx")
	}else{		//язык указан в URL
		var	s=location.pathname.substr(0,4);	//первые 4 символа URL после доменного имени
		s=(
			s.search(/\/[a-z]{2}\//)==-1?	//соответствие шаблону "две лат.буквы между слэшами" ("/ru/", /en/)
			"/?ajx"				//для основного языка
			:s+"?ajx"			//для других языков — например: "/en/?ajx"
		);
	}
	post(s,f,formData,'formData','POST');
	return false;
};

function showDiv(txt,a,modal){	//txt=блок как в ajx() и showImg(), a.focus()
	var e=document.createElement("DIV"),m=document.createElement("DIV"),n=document.createElement("CENTER");
	document.body.appendChild(e);
	e.appendChild(m);
	e.appendChild(n);
	function r(){if(e && e.parentNode){e.parentNode.removeChild(e)};document.body.removeEventListener('keydown',keydown);if(a){a.focus()}}
	function f(){e.style.opacity=0;window.setTimeout(r,500)}
	function op(){
		var t=(window.innerHeight-n.offsetHeight)/2;	//(document.body.clientHeight-n.offsetHeight)/2;
		if(t<20){t=20}
		var top=document.documentElement.scrollTop || document.body.scrollTop;
		//document.title=top;
		n.style.top=top+t+"px";
		//n.style.top=t+"px";

		t=(window.innerWidth-n.offsetWidth)/2;
		if(t<0){t=0}
		n.style.left=t+"px";
		e.style.opacity=1
	}
	e.className="ajxAlert";
	n.innerHTML=txt;
	window.setTimeout(op,99);	//плавное отображение, без задержки не работает (хоть 0) 
	//var div=e.lastChild;

	if(!modal){e.addEventListener('click',f);}	//закрывать при нажатии на маску

	function sP(evt){evt.stopPropagation()}	
	n.addEventListener('click',sP);

	if(n.lastChild){n.lastChild.addEventListener('click',f);}	//и на крестик

	function keydown(evt){if(evt.keyCode==27){f()}}	//и на клавишу [Esc]
	document.body.addEventListener('keydown',keydown);
	return n
}

function Confirm(txt,f,L){
	if(!L){L=['Да','Нет']}
	txt='<center><div onclick="event.stopPropagation()" style="padding:15px">'
	+txt+'</div><button style="width:40%">'+L[0]+'</button> <button style="width:40%">'+L[1]+'</button><a class=close>&times;</a></center>';

	var div=showDiv(txt);
	var e=div.getElementsByTagName("button");
	e[0].addEventListener('click',f);
	e[0].focus();
	//e[0].addEventListener('keyDown',f);
	return false
}

function Alert(txt,className,a){	//a.focus()
	txt='<center'+(className?' class="'+className+'"':'')+'>'
	+txt+'<a class=close>&times;</a></center>';
	var div=showDiv(txt,a);
	return false
}

function openWin(a){
	var	s="resizable,scrollbars=yes,status=1"
		,e=window.open(a.href,a.target,s);
	e.focus();
	window.setTimeout(f,999);
	function f(){e.focus()}
}

function jsAppend(a,b,f,c,host){	//a=имя скрипта без .js; b если нужно удалить скрипт, если уже загружался; f=функция на onload; c='js/filename.php?get'
	var n=g("js"+a);
	if(b && n){n.parentNode.removeChild(n);n=0;}
	if(n){
		if(typeof(f)=='function'){f()}
		return true
	}else{
		var e=document.createElement("SCRIPT");
		e.src=(host?host:"")+"/"+(c?c:"js/"+a+".js");
		e.id="js"+a;
		document.head.appendChild(e);
		if(typeof(f)=='function'){
			e.onload=f	//top.setTimeout(f,999)
		}
		return e
	}
}

function ajxTo(a,e,f,f1){	//выполнить js в блок e из a
	e.style.display=(e.style.display=='none'?(e.dataset && e.dataset.s?e.dataset.s:''):'none');
	if(e.innerHTML.length<9 || e.firstChild.nodeName=='IMG'){f()}else if(f1){f1()}
	return false
}

function nn(n){
	if(n<10){n='0'+n;}
	return n
}
function FormatNumber(s){	//между тысячами вставляет #160
	s=s+'';
	var A=[],j=0;
	for(i=s.length-1;i>=0;i--){
	 j++;
	 A.push(s[i])
	 if(j%3==0){A.push(' ')}
	}
	A.reverse();
	return A.join("").trim()
}

function g(a){return document.getElementById(a)}

function chk(a,b){
	var e=a.previousSibling;e.checked=!e.checked;
	if(e.onclick){e.onclick()}
	if(b){e.form.onsubmit()}
}



String.prototype.hashCode = function(){	//http://werxltd.com/wp/2010/05/13/javascript-implementation-of-javas-string-hashcode-method/
	var hash=0,i=0,char;		//http://www.queryadmin.com/1611/java-hashcode-php-javascript/	//php not for UTF-8
	if(this.length==0){return hash}
	for(i;i<this.length;i++){
		char=this.charCodeAt(i);
		hash=((hash<<5)-hash)+char;
		hash=hash & hash;	//Convert to 32bit integer
	}
	return hash;
}