function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(location.search);
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}

// Get the name for the current edah and block IDs.
$(function() {
	var edahId = getParameterByName('edah');
	var blockId = getParameterByName('block');
        $.ajax({
                url: 'levelingAjax.php',
                    type: 'post',
                    data:{ names_for_id: 1,
		           edah_id: edahId, 
			   block_id: blockId },
                    success: function(json) {
                    $( ".edahfill" ).text(function() {
                            if (json.edahName &&
                                json.edahName.length > 0) {
                                return $(this).text().replace("EDAH", json.edahName);
                            }
                        });
		    $( ".blockfill" ).text(function() {
                            if (json.blockName &&
                                json.blockName.length > 0) {
                                return $(this).text().replace("BLOCK", json.blockName);
                            }
                        });
                },
                    error: function(xhr, desc, err) {
		    console.log(xhr);
		    console.log("Details: " + desc + "\nError:" + err);
                }
            })
	    });

// Action for the Cancel button.
$(function() {
        $("#Cancel").click(function(event) {
                event.preventDefault();
		window.location.replace("staffHome.php");
	    })
	    });

// Action for the Reassign button.
$(function() {
	var edah = getParameterByName("edah");
	var block = getParameterByName("block");
        $("#Reassign").click(function(event) {
                event.preventDefault();
		$.ajax({
                        url: 'levelingAjax.php',
			    type: 'post',
			    data:{reassign: 1, edah: edah, block: block},
			    success: function(data) {
			    // Fade and then reload with new data (for multiple clicks).
			    $( "#results:visible" ).removeAttr( "style" ).fadeOut();
                            $( "#results" ).html(function() {
                                    txt = $(this).html().replace("UNDERMIN", data.under_min_list);
				    txt = txt.replace("OVERMAX", data.over_max_list);
                                    return txt.replace("STATS", data.statstxt);
                                });
                            $( "#results" ).show("slide", 500);
			    $( "#results" ).attr('disabled', false);
                        },
                            error: function() {
                            $( "#results" ).text("Oops! The system was unable to reassign.  Please hit Submit again.  If the problem persists, please contact the administrator.  Error: data.err");			    
                            $( "#results" ).show("slide", 250 );
                        }
                    });
            })
	    });
