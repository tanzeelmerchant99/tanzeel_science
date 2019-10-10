function wppq_confirm_delete() {
	if (confirm('Click OK to delete all WP Pro Quiz statistics in the Database. THIS ACTION CANNOT BE UNDONE!')) {
		document.getElementById("deleteAllStatistics").submit();
        exit;
    } else {
        return false;
    }
}

$(document).ready(function () {
    var selectcounter = 1;
    
    $(".selectable").each(function() {
        idja = "selectable" + selectcounter;
        $(this).attr('id', idja);
        $(this).attr('onclick', 'selectText("' + idja + '")');
        selectcounter++;
    });     
});

function selectText(containerid) {
    if (document.selection) {
        var range = document.body.createTextRange();
        range.moveToElementText(document.getElementById(containerid));
        range.select();
        document.execCommand("copy");
        alert("Test statistics copied to clipboard!")
    } else if (window.getSelection) {
        var range = document.createRange();
        range.selectNode(document.getElementById(containerid));
        window.getSelection().addRange(range);
        document.execCommand("copy");
        alert("Test statistics copied to clipboard!")
    }
}
