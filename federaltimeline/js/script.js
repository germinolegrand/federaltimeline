function searchDateInstance(date, instance) {
	return document.querySelector('#timeline .date-instance[data-date="'+date+'"][data-instance="'+instance+'"]');
}

function searchDateInstanceBefore(date) {
	var dateInstanceArray = document.querySelectorAll("#timeline .date-instance");
	for (var i = 0; i < dateInstanceArray.length; i++) {
		if(dateInstanceArray[i].dataset.date <= date){
			return dateInstanceArray[i];
		}
	}
	return null;
}

/*
* @param String date
*/
function convertDateToView(date) {
	return (new Date(parseInt(date))).toLocaleDateString("fr-FR");
}

/*
* @param String date
*/
function convertDateFromView(date) {
	return Math.floor(Date.parse(date.replace(/([0-9]+)\/([0-9]+)\/([0-9]+)/,'$3-$2-$1T00:00:00Z'))/1000);
}

function createDateInstance(date, instance) {
	var dateInstance = document.createElement("div");
	dateInstance.classList.add("date-instance");
	dateInstance.dataset.date = date;
	dateInstance.dataset.instance = instance;
	{
		var dateElem = document.createElement("div");
		dateElem.classList.add("date");
		dateElem.textContent = convertDateToView(date);
		dateInstance.append(dateElem);
		var instanceElem = document.createElement("div");
		instanceElem.classList.add("instance");
		instanceElem.textContent = instance;
		instanceElem.addEventListener('click', function() {
			filterByDateInstance(dateInstance.classList.contains('filtered') ? '' : instance);
		});
		dateInstance.append(instanceElem);
		var filesElem = document.createElement("div");
		filesElem.classList.add("files");
		{
			filesFlexElem = document.createElement("div");
			filesFlexElem.classList.add("files-flex");
			filesFlexElem.innerHTML = '<input type="file" multiple style="display: none" />';
			{
				var buttonAdd = document.createElement("button");
				buttonAdd.classList.add("icon-add");
				buttonAdd.addEventListener('click', function(event) {
					onButtonAddFile(this);
				});
				filesFlexElem.append(buttonAdd);
			}
			filesElem.append(filesFlexElem);
		}
		dateInstance.append(filesElem);
	}
	return dateInstance;
}

function appendFile(date, instance, fileName, fileId) {
	var dateInstance = searchDateInstance(date, instance);
	if(dateInstance == null){
		dateInstance = createDateInstance(date, instance);
		var diBefore = searchDateInstanceBefore(date);
		if(diBefore != null){
			diBefore.before(dateInstance);
		} else {
			document.querySelector("#timeline").append(dateInstance);
		}
	}
	var filesFlexElem = dateInstance.querySelector('.files-flex');
	// append the file
	var fileElem = document.createElement("div");
	fileElem.classList.add("file");
	fileElem.dataset.fileid = fileId;
	fileElem.innerHTML = '<a href="../files?fileid='+fileId+'"><i class="icon-file" aria-hidden="true"></i>'+fileName+'</a>';
	fileElem.querySelector('a').addEventListener('click', function(e) {
		e.preventDefault();
		getTimelineFileDownload(fileId);
	});
	filesFlexElem.prepend(fileElem);
	// update input
	appendInputInstanceListOption(instance);
}

function appendUntaggedFile(date, instance, fileName, fileId) {
	var untaggedFile = document.createElement('form');
	untaggedFile.classList.add("untagged-file");
	untaggedFile.dataset.fileid = fileId;
	untaggedFile.innerHTML = '<input type="text" pattern="(0[1-9]|1[0-9]|2[0-9]|3[01]).(0[1-9]|1[012]).[0-9]{4}" name="date" placeholder="DD/MM/YYYY" required />'
		+ '<input type="text" name="instance" list="inputInstanceList" placeholder="Instance" required />'
		+ '<a href="#" class="file"><i class="icon-file" aria-hidden="true"></i>'+fileName+'</a>'
		+ '<input class="icon-checkmark" type="submit" name="submit" value="" />';
	untaggedFile.querySelector('a').addEventListener('click', function() {
		getTimelineFileDownload(fileId);
	});
	untaggedFile.addEventListener('submit', function(e) {
		e.preventDefault();
		var form = this;
		var fd = new FormData;
		fd.set('fileId', fileId);
		fd.set('date', convertDateFromView(form.querySelector('input[name="date"]').value));
		fd.set('instance', form.querySelector('input[name="instance"]').value);
		postTimelineFileTags(fd, function() {
			appendFile(fd.get('date')*1000, fd.get('instance'), fileName, fileId);
			form.remove();
			updateUntaggedCount();
		});
	});
	document.querySelector("#untagged-list").append(untaggedFile);
}

function appendInputInstanceListOption(instance) {
	if(document.querySelector('#inputInstanceList > option[value="'+instance+'"]')){
		return;
	}
	var inputInstanceList = document.querySelector('#inputInstanceList');
	var option = document.createElement('option');
	option.value = instance;
	inputInstanceList.append(option);
}

function filterByDateInstance(filterExp) {
	var regex;
	try{
		regex = new RegExp(filterExp);
	} catch(e){
		return;
	}
	var dateInstanceArray = document.querySelectorAll('#timeline .date-instance');
	for (var i = 0; i < dateInstanceArray.length; i++) {
		var date = dateInstanceArray[i].dataset.date;
		date = (new Date(parseInt(date))).toLocaleDateString();
		if(filterExp == '' || regex.test(date) || regex.test(dateInstanceArray[i].dataset.instance)){
			dateInstanceArray[i].style.display = '';
			dateInstanceArray[i].classList.toggle('filtered', filterExp != '');
		} else {
			dateInstanceArray[i].style.display = 'none';
		}
	}
}

/*
* @param FormData fd
* @param Function doneCallback()
* @param Function failCallback()
*/
function postTimelineFileTags(fd, doneCallback, failCallback) {
	$.ajax('api/1.0/file/tags', {
		type: 'POST',
		processData: false,
		contentType: false,
		data: fd
	}).done(doneCallback).fail(failCallback);
}

/*
* @param FormData fd
*/
function postTimelineFileUpload(fd) {
	$.ajax('api/1.0/file', {
		type: 'POST',
		processData: false,
		contentType: false,
		data: fd
	}).done(function(data, textStatus, jqXHR) {
		for (var i = 0; i < data.length; i++) {
			appendFile(fd.get('date')*1000, fd.get('instance'), data[i]['name'], data[i]['id']);
		}
		// Clear input file
		$inputFile = $('#tlUpload input[type="file"]');
		$inputFile.wrap('<form>').closest('form').get(0).reset();
		$inputFile.unwrap();
	});
}

/*
* @param int fileId
*/
function getTimelineFileDownload(fileId) {
	var req = new XMLHttpRequest();
	req.open("GET", "api/1.0/file?fileId="+fileId, true);
	req.responseType = "blob";
	req.setRequestHeader('requesttoken', oc_requesttoken);

	req.onload = function (event) {
		var blob = req.response;
		var link=document.createElement('a');
		document.body.appendChild(link);
		link.href=window.URL.createObjectURL(blob);
		var filename = '';
		var filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
        var matches = filenameRegex.exec(req.getResponseHeader('Content-Disposition'));
        if (matches !== null && matches[1]) filename = matches[1].replace(/['"]/g, '');
		link.download = filename;
		link.click();
	};

	req.send();
}

function onButtonAddFile(button) {
	var dateInstance = button.parentElement.parentElement.parentElement;
	button.previousElementSibling.addEventListener('change', function(){
		if(this.value == ""){
			return;
		}
		var fd = new FormData();
		for (var i = 0; i < this.files.length; i++) {
			fd.append("file[]", this.files[i]);
		}
		fd.append("date", Math.floor(dateInstance.dataset.date/1000));
		fd.append("instance", dateInstance.dataset.instance);
		fd.append("uploadFolder", document.querySelector('#uploadFolder').value);
		postTimelineFileUpload(fd);
		this.value = "";
	});
	button.previousElementSibling.click();
	
}

function updateUntaggedCount() {
	var untaggedCount = document.querySelectorAll('#untagged-list .untagged-file').length;
	document.querySelector('.untagged-count').textContent = untaggedCount;
	if(untaggedCount == 0){
		document.querySelector('.show-untagged').setAttribute('disabled', 'true');
	} else {
		document.querySelector('.show-untagged').removeAttribute('disabled');
	}
}

$().ready(function() {
	$.ajax('api/1.0/timeline').done(function(data, textStatus, jqXHR) {
		var dateInstances = data['dateInstances'];
		for (var i = 0; i < dateInstances.length; i++) {
			appendFile(dateInstances[i]['di_date']*1000, dateInstances[i]['di_instance'], dateInstances[i]['name'], dateInstances[i]['id']);
		}
		var untaggedFiles = data['untaggedFiles'];
		for (var i = 0; i < untaggedFiles.length; i++) {
			appendUntaggedFile(untaggedFiles[i]['di_date']*1000, untaggedFiles[i]['di_instance'], untaggedFiles[i]['name'], untaggedFiles[i]['id']);
		}
		updateUntaggedCount();
		var uploadFolder = data['uploadFolder'];
		document.querySelector('#uploadFolder').value = uploadFolder;
	});

	$('#tlUpload').on('submit', function(e) {
		e.preventDefault();
		var fd = new FormData(this);
		fd.set('date', convertDateFromView(fd.get('date')));
		fd.set('name', $('#tlUpload input[type="file"]')[0].value);
		var files = $('#tlUpload input[type="file"]')[0].files;
		fd.append("uploadFolder", document.querySelector('#uploadFolder').value);
		postTimelineFileUpload(fd);
	});

	document.querySelector('#instance-filter').addEventListener('input', function() {
		filterByDateInstance(this.value);
	});

	document.querySelector('.show-untagged').addEventListener('click', function() {
		document.querySelector('#untagged-list').classList.toggle('show');
	});
});