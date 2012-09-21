<script type="text/javascript">
function showLoader(id) { jQuery(id).show(); }
function hideLoader(id) { jQuery(id).hide(); }  

// wait for the DOM to be loaded 
jQuery(document).ready(function() {
    // SWF handler
    jQuery('#uploadFormSWF').submit(function() { 
      var options = { 
      dataType: 'json',
      beforeSubmit: function() { showLoader('#loadimgswf'); },
      success: showResponseSWF       
      };       
      jQuery(this).ajaxSubmit(options); 
      return false; 
    });
    // Thumbnail handler
    jQuery('#uploadFormTHUMB').submit(function() { 
      var options = { 
      dataType: 'json',
      beforeSubmit: function() { showLoader('#loadimgthumb'); },
      success: showResponseTHUMB       
      };       
      jQuery(this).ajaxSubmit(options); 
      return false; 
    });
    // Screenshot handler
    jQuery('#uploadFormSCREEN').submit(function() { 
      var options = { 
      dataType: 'json',
      beforeSubmit: function() { showLoader('#loadimgscreen'); },
      success: showResponseSCREEN       
      };       
      jQuery(this).ajaxSubmit(options); 
      return false; 
    });
    // TAR handler
    jQuery('#uploadFormTAR').submit(function() { 
      var options = { 
      dataType: 'json',
      beforeSubmit: function() { showLoader('#loadimgtar'); },
      success: showResponseTAR
      };       
      jQuery(this).ajaxSubmit(options); 
      return false; 
    });
    // ZIP handler
    jQuery('#uploadFormZIP').submit(function() { 
      var options = { 
      dataType: 'json',
      beforeSubmit: function() { showLoader('#loadimgzip'); },
      success: showResponseZIP
      };       
      jQuery(this).ajaxSubmit(options); 
      return false; 
    });
    // Embed handler
    jQuery('#uploadFormEMIF').submit(function() { 
      var options = { 
      dataType: 'json',
      beforeSubmit: function() { showLoader('#loadimgemif'); },
      success: showResponseEMIF
      };       
      jQuery(this).ajaxSubmit(options); 
      return false; 
    });    
    
});

function showResponseSWF(data, statusText, xhr, $form)  { 
  hideLoader('#loadimgswf');

  // Check the status
  if (statusText == 'success' && data.error == '') {
    jQuery('#filename').html('<strong>' + data.name + '</strong> - <i>' + data.info_dim + '</i>');
    jQuery('#gamewidth').val(data.width);
    jQuery('#gameheight').val(data.height);
    jQuery('#gamename').val(data.realname);
    jQuery('#importgame').val(data.location_url);
    jQuery('#importtype').val(data.type);
  }
  else {
    if ( statusText != 'success' ) {
      alert('Error: ' + statusText);
    }
    else {
      alert('Error: ' + data.error);
    }
  }  
}

function showResponseTHUMB(data, statusText, xhr, $form)  { 
  hideLoader('#loadimgthumb');

  // Check the status
  if (statusText == 'success' && data.error == '') {
    jQuery('#filenamethumb').html('<img src="' + data.thumb_url + '" alt=""  />');
    jQuery('#importthumb').val(data.thumb_url);    
  }
  else {
    if ( statusText != 'success' ) {
      alert('Error: ' + statusText);
    }
    else {
      alert('Error: ' + data.error);
    }
  }  
}

function showResponseSCREEN(data, statusText, xhr, $form)  { 
  var output_string = '';
  
  hideLoader('#loadimgscreen');

  // Check the status
  if (statusText == 'success' && data.error == '') {
    for (var i=0;  i<=3; i++) {
      if (data.screen_name[i] != '') {
        var x = i + 1;
        output_string += '<strong>Screen ' + x + ': ' + data.screen_name[i] + '</strong><br />';
        jQuery('#importscreen' + x).val(data.screen_url[i]);
      }
      else {
        output_string += data.screen_error[i] + '<br />';
      }
    }              
    jQuery('#filenamescreen').html(output_string);
  }
  else {
    if ( statusText != 'success' ) {
      alert('Error: ' + statusText);
    }
    else {
      alert('Error: ' + data.error);
    }
  }  
}

function showResponseTAR(data, statusText, xhr, $form)  { 
  hideLoader('#loadimgtar');

  // Check the status
  if (statusText == 'success' && data.error == '') {   
    var thumb = '<img src="'+data.thumbnail_url+'" alt="" />'; 
    jQuery('#filenametar').html('<strong>' + data.name + '</strong> - <i>' + data.info_dim + '</i><br />' + thumb);
    jQuery('#gamewidth').val(data.width);
    jQuery('#gameheight').val(data.height);
    jQuery('#gamename').val(data.realname);
    jQuery('#importgame').val(data.location_url);
    jQuery('#importthumb').val(data.thumbnail_url);
    jQuery('#importtype').val(data.type);
    jQuery('#gamedescr').val(data.description);
    jQuery('#gameinstr').val(data.instructions);
    jQuery('#lbenabled').val(data.leaderboard_enabled);
    jQuery('#highscoretype').val(data.highscore_type);
    jQuery('#slug').val(data.slug);     
  }
  else {
    if ( statusText != 'success' ) {
      alert('Error: ' + statusText);
    }
    else {
      alert('Error: ' + data.error);
    }
  }  
}

function showResponseZIP(data, statusText, xhr, $form)  { 
  hideLoader('#loadimgzip');

  // Check the status
  if (statusText == 'success' && data.error == '') {  
    var thumb = '<img src="'+data.thumbnail_url+'" alt="" />'; 
    jQuery('#filenamezip').html('<strong>' + data.name + '</strong> - <i>' + data.info_dim + '</i><br />' + thumb);
    jQuery('#gamewidth').val(data.width);
    jQuery('#gameheight').val(data.height);
    jQuery('#gamename').val(data.realname);
    jQuery('#importgame').val(data.location_url);
    jQuery('#importthumb').val(data.thumbnail_url);
    jQuery('#importtype').val(data.type);
    jQuery('#slug').val(data.slug);      
  }
  else {
    if ( statusText != 'success' ) {
      alert('Error: ' + statusText);
    }
    else {
      alert('Error: ' + data.error);
    }
  }  
}

function showResponseEMIF(data, statusText, xhr, $form)  { 
  hideLoader('#loadimgemif');

  // Check the status
  if (statusText == 'success' && data.error == '') {  
    jQuery('#importtype').val(data.type);
    jQuery('#importgame').val(data.importgame);
    jQuery('#filenameemif').html('<strong>' + data.result + '</strong>');    
  }
  else {
    if ( statusText != 'success' ) {
      alert('Error: ' + statusText);
    }
    else {
      alert('Error: ' + data.error);
    }
  }  
}
/** Ende Upload Test **/
  

function myarcade_chkImportCustom() {
  if (document.FormCustomGame.importgame.value == "") {
    alert("<?php _e("No game file added!", MYARCADE_TEXT_DOMAIN); ?>");
    return false;
  }  
  if (document.FormCustomGame.importthumb.value == "") {
    alert("<?php _e("No thumbnail added!", MYARCADE_TEXT_DOMAIN); ?>");
    return false;
  }   
  if (document.FormCustomGame.gamename.value == "") {
    alert("<?php _e("Game name not set!", MYARCADE_TEXT_DOMAIN); ?>");
    document.FormCustomGame.gamename.focus();
    return false;
  }
  if (document.FormCustomGame.gamedescr.value == "") {
    alert("<?php _e("There is no game description!", MYARCADE_TEXT_DOMAIN); ?>");
    document.FormCustomGame.gamedescr.focus();
    return false;
  }

  var categs = false;
  for(var i = 0; i < document.FormCustomGame.elements.length - 1; i++) {
    if( (document.FormCustomGame.elements[i].type == "checkbox") && (document.FormCustomGame.elements[i].checked == true)) {
      categs = true;
      break;
    }
  }

  if (categs == false) {
    alert("<?php _e("Select at least one category!", MYARCADE_TEXT_DOMAIN);?>");
    return false;
  }

  return true;
} // END - myarcade_chkImportCustom


/* Import method selection */
jQuery(document).ready(function() {
  jQuery('#importibparcade').hide();
  jQuery('#importphpbb').hide();
  jQuery('#importembedif').hide();
  jQuery('#importmethod').change( function() {
    jQuery('#filename').html('');
    jQuery('#filenametar').html('');
    jQuery('#gamewidth').val('');
    jQuery('#gameheight').val('');
    jQuery('#gamename').val('');
    jQuery('#importgame').val('');
    jQuery('#importtype').val(''); 
    jQuery('#importscreen1').val('');
    jQuery('#importscreen2').val('');
    jQuery('#importscreen3').val('');
    jQuery('#importscreen4').val('');
    jQuery('#lbenabled').val('');
    jQuery('#highscoretype').val('');
    jQuery('#slug').val('');   
    
    switch (this.value) {
      case 'importibparcade': {
        jQuery('#importswfdcr').hide();
        jQuery('#importembedif').hide();
        jQuery('#thumbform').hide();
        jQuery('#importphpbb').hide();
        jQuery('#importibparcade').fadeIn('slow');        
      }
      break;
      
      case 'importphpbb': {
        jQuery('#importswfdcr').hide();
        jQuery('#importembedif').hide();
        jQuery('#thumbform').hide();
        jQuery('#importphpbb').fadeIn('slow');
        jQuery('#importibparcade').hide();        
      }
      break;      
      
      case 'importswfdcr': {
        jQuery('#importibparcade').hide();
        jQuery('#importembedif').hide();
        jQuery('#importphpbb').hide();
        jQuery('#importswfdcr').fadeIn('slow');
        jQuery('#thumbform').fadeIn('slow');
      }
      break;
      
      case 'importembedif': {
        jQuery('#importibparcade').hide();
        jQuery('#importswfdcr').hide();
        jQuery('#importphpbb').hide();
        jQuery('#importembedif').fadeIn('slow'); 
        jQuery('#thumbform').fadeIn('slow');
               
      }
      break;
    }
  });
  jQuery('#importmethod').change();
});
</script>  