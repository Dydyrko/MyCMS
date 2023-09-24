<?php
$L=array('en'=>'Create an admin account','uk'=>'Створити обліковий запис адміну','ru'=>'Создать учётную запись админа');
echo
'<style>input[name=mail]:invalid{color:red}</style>'.
'<form onsubmit="
	var e=elements,i;
	for(i=0;i<e.length;i++){
		if(e[i].name && e[i].value==\'\'){
			e[i].focus();return false
		}
	}
	window.lang=\'setup\';
	return ajxFormData(event,this,0,nextSibling)
" autocomplete="off">'
	.'<input name=CMS value="addPerson" type=hidden>'
	.'<table>'
		.'<tr><td>E-mail<td><input name=mail type=mail pattern=".+@.+\..+" autocomplete="off">'
		.'<tr><td style="cursor:pointer;color:blue"'
			.' onclick="let n=nextSibling.firstChild;if(n.type==`password`){n.type=`text`}else{n.type=`password`}"'
		.'>Password<td><input name=psw type=password autocomplete="new-password">'
		.'<tr><td colspan=2><button>'.$L[$lang].'</button>'
	.'</table>'
	.'<div></div>'
.'</form>';