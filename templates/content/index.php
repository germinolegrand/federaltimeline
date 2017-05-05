<form id="tlUpload" enctype="multipart/form-data">
	<input type="text" pattern="(0[1-9]|1[0-9]|2[0-9]|3[01]).(0[1-9]|1[012]).[0-9]{4}" name="date" placeholder="DD/MM/YYYY" required /><input type="text" name="instance" list="inputInstanceList" placeholder="Instance" required /><input type="file" name="file[]" multiple required /><span></span><input type="submit" name="submit" value="Ajouter" />
</form>
<div id="untagged-list"></div>
<datalist id="inputInstanceList"></datalist>
<div id="timeline">
	<div class="line"></div>
</div>