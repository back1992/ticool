/*
 * ARI Quiz
 *
 * @package		ARI Quiz
 * @version		1.0.0
 * @author		ARI Soft
 * @copyright	Copyright (c) 2009 www.ari-soft.com. All rights reserved
 * @license		GNU/GPL (http://www.gnu.org/copyleft/gpl.html)
 * 
 */

function tableOrdering(sortField, sortDir, task)
{
	if (!task) task = document.adminForm.task.value;
	var obj = document.getElementById('zfield');
	if (!obj)
	{
		obj = ari_createElementWithName('input', 'zfield');
		obj.type = 'hidden'; 
		obj.value = sortField;
		document.adminForm.appendChild(obj);
	}	
	obj = document.getElementById('zdir');
	if (!obj)
	{
		obj = ari_createElementWithName('input', 'zdir');
		obj.type = 'hidden'; 
		obj.value = sortDir;
		document.adminForm.appendChild(obj);
	}
	obj = document.getElementById('zsort');
	if (!obj)
	{
		obj = ari_createElementWithName('input', 'zsort');
		obj.type = 'hidden'; 
		obj.value = '';
		document.adminForm.appendChild(obj);
	}

	if (typeof(Joomla) != "undefined" && Joomla.submitbutton)
		Joomla.submitbutton(task);
	else
		submitbutton(task);
}

function ari_createElementWithName(type, name)
{
	var el;
	try
	{
		el = document.createElement('<' + type + ' name="' + name + '" />');
	}
	catch (e) {}
		
	if (!el ||
		!el.name ||
		el.name != name)
	{
		el = document.createElement(type);
		el.name = name;
	}
	
	el.id = name;
		
	return el;
}