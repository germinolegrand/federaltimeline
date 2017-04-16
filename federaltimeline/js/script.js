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

function createDateInstance(date, instance) {
	var dateInstance = document.createElement("div");
	dateInstance.classList.add("date-instance");
	dateInstance.dataset.date = date;
	dateInstance.dataset.instance = instance;
	{
		var dateElem = document.createElement("div");
		dateElem.classList.add("date");
		dateElem.textContent = (new Date(parseInt(date))).toLocaleDateString();
		dateInstance.append(dateElem);
		var instanceElem = document.createElement("div");
		instanceElem.classList.add("instance");
		instanceElem.textContent = instance;
		dateInstance.append(instanceElem);
		var filesElem = document.createElement("div");
		filesElem.classList.add("files");
		{
			filesFlexElem = document.createElement("div");
			filesFlexElem.classList.add("files-flex");
			filesFlexElem.innerHTML = '<input type="file" style="display: none" />';
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

function appendFile(date, instance, file) {
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
	fileElem.innerHTML = '<a href="#"><i class="fa fa-file" aria-hidden="true"></i> '+file+'</a>';
	filesFlexElem.prepend(fileElem);
}

function onButtonAddFile(button) {
	var dateInstance = button.parentElement.parentElement.parentElement;
	button.previousElementSibling.addEventListener('change', function(){
		if(this.value == ""){
			return;
		}
		console.log("change:"+this.value);
		appendFile(dateInstance.dataset.date, dateInstance.dataset.instance, this.value);
		this.value = "";
	});
	button.previousElementSibling.click();
	
}

$().ready(function() {
	appendFile(Date.now(), "Secrétariat Fédéral", "Trololo");
	$.ajax('api/1.0/timeline').done(function(data, textStatus, jqXHR) {
		console.log(data);
		for (var i = 0; i < data.length; i++) {
			appendFile(data[i]['di_date'], data[i]['di_instance'], data[i]['name']);
		}
	}).fail(function(data, textStatus, jqXHR) {
		console.log(textStatus);
	}).always(function(data, textStatus, jqXHR) {
		
	});
});