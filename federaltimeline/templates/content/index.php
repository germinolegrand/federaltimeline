<div id="timelineHeader">
	<h1>Timeline Fédérale</h1>
	<form id="tlUpload" enctype="multipart/form-data">
		<input type="text" pattern="(0[1-9]|1[0-9]|2[0-9]|3[01]).(0[1-9]|1[012]).[0-9]{4}" name="date" required /><input type="text" name="instance" required /><input type="file" name="file" required /><input type="submit" name="submit" value="Ajouter" />
	</form>
	<input type="text" id="instanceFilter" />
	<div id="timeline">
		<div class="line"></div>
	</div>
</div>