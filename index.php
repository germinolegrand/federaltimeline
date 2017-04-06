<!DOCTYPE html>
<html>
<head>
	<title>Timeline Fédérale - Solidaires étudiant-e-s</title>
	<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
	<style type="text/css">
html {
	background-color: blanchedalmond;
}
#timeline {
	position: relative;
}

#timeline > .line {
	position: absolute;
	left: 0;
	top: 0;
	width: 0.2em;
	height: 100%;
	background-color: green;
}

#timeline > .date-instance {
	position: relative;
}

.date {
	display: inline-block;
	position: relative;
	margin-left: 1.2em;
	padding: 0.1em;
	border-radius: 0.4em;
	background-color: white;
	width: 5em;
	text-align: center;
}

.date::before {
	content: " ";
	position: absolute;
	width: 1.2em;
	height: 0.2em;
	right: 100%;
	bottom: calc(50% - 0.2em / 2);
	background-color: green;
}

.instance {
    display: inline-block;
    position: relative;
    margin-left: 0.5em;
    padding: 0.1em 0.2em;
    border-radius: 0.4em;
    background-color: white;
}

.instance::before {
	content: " ";
	position: absolute;
	width: 0.5em;
	height: 0.2em;
	right: 100%;
	bottom: calc(50% - 0.2em / 2);
	background-color: white;
}

.files {
	display: inline-block;
	position: relative;
    margin-left: 0.5em;
    vertical-align: top;
	max-width: 60%;
}

.files::before {
	content: " ";
	position: absolute;
	width: 0.5em;
	height: 0.2em;
	right: 100%;
	top: 0.6em;
	background-color: white;
}

.files-flex {
    display: inline-flex;
    flex-flow: row wrap;
    overflow: hidden;
    border-radius: 0.4em;
    background-color: white;
}

.files-flex > * {
	display: inline-block;
    padding: 0.1em 0.2em;
}

.files-flex > button {
	padding: 0;
	background-color: white;
	color: black;
	border-radius: 0 0.4em 0.4em 0;
}

a {
	color: inherit; /* blue colors for links too */
	text-decoration: inherit; /* no underline */
}

	</style>
</head>
<body>

<div id="timelineHeader">
	<h1>Timeline Fédérale</h1>
	<div id="timeline">
		<div class="line"></div>
		<div class="date-instance">
			<div class="date">12/03/2017
			</div><div class="instance">Secrétariat Fédéral
			</div><div class="files">
				<div class="files-flex">
					<div class="file">
						<a href="#"><i class="fa fa-file" aria-hidden="true"></i> CR SF tel</a>
					</div><div class="file">
						<a href="#"><i class="fa fa-file" aria-hidden="true"></i> CDD </a>
					</div><button onclick="onButtonAddFile(this)"><i class="fa fa-plus"></i></button>
				</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
function appendFile(date, instance, file) {
	var dateInstance = document.createElement("div");
	dateInstance.classList.add("date-instance");
	{
		var dateElem = document.createElement("div");
		dateElem.classList.add("date");
		dateElem.innerHTML = date.toLocaleDateString();
		dateInstance.append(dateElem);
		var instanceElem = document.createElement("div");
		instanceElem.classList.add("instance");
		instanceElem.innerHTML = instance;
		dateInstance.append(instanceElem);
		var filesElem = document.createElement("div");
		filesElem.classList.add("files");
		{
			var filesFlexElem = document.createElement("div");
			filesFlexElem.classList.add("files-flex");
			filesFlexElem.innerHTML = '<button onclick="onButtonAddFile(this)"><i class="fa fa-plus"></i></button>';
			{
				var fileElem = document.createElement("div");
				fileElem.classList.add("files");
				fileElem.innerHTML = file;
				filesFlexElem.prepend(fileElem);
			}
			filesElem.append(filesFlexElem);
		}
		dateInstance.append(filesElem);
	}
	document.querySelector("#timeline").append(dateInstance);
}

function onButtonAddFile(button) {
	var dateInstance = button.parentElement.parentElement.parentElement;
	appendFile(new Date(dateInstance.querySelector(".date").innerHTML), dateInstance.querySelector(".instance").innerHTML.trim(), "Yololo");
}
</script>
</body>
</html>