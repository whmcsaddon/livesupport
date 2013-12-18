/*	
	jQuery uploadprogress v 0.3
	copyright (c) 2008,2009 Jolyon Terwilliger
	
	requires a web server running PHP 5.2.x with the 
	PHP uploadprogress module compiled in or enabled as a shared object:
	http://pecl.php.net/package/uploadprogress
	
	Dual licensed under the MIT and GPL licenses:
	http://www.opensource.org/licenses/mit-license.php
	http://www.gnu.org/licenses/gpl.html

basic description:

	a plugin to augment a standard file upload form with transparent background upload
and add uploadprogress meter to keep client informed of progress.  (see requirements above)

usage:

	jQuery('form#upload_form').uploadProgress({ id:'uniqueid' | keyLength:11 });

	if no id is passed, a key of keyLength or 11 characters is generated and applied to the target form as a hidden field to key the upload session.


parameters:

	id - default: none - optional.  will be generated if omitted.
	
	keyLength - default: 11 - length of UPLOAD_IDENTIFIER hidden input field key to be generated.

	dataFormat - default: 'json' - only viable option at this point would be jsonp (qv.)

	progressURL - default: none - this is the relative or absolute URL used for the uploadprogress update post request.

	updateDelay - default: 1000 - in milliseconds - regardless how low this value is set, the previous uploadprogress request must finish before the next will be sent. This is how long to wait until the next request is started. If your server is particularly slow or you have high network latency issues, setting a lower value like 200 can simulate faster updates.
	
	notFoundLimit - default: 5 - how many loops to allow any return value of 'error' before exiting with failed status.  Sometimes the first uploadprogress request is processed by the webserver before the actual upload has been acknowledged and started in the system, potentially returning an 'upload_id not found' error.  This is the threshold value to set for # of error messages to allow before failing the upload.
	
	waitText - default: false - If set, this text will be used replace form submit button value text.  Note:  a flag is set in within the plugin to prevent double-submit actions on the form.  Once submit is completed, the original text is restored for future submits.

	debugDisplay - default: none - if set, used as a selector for DOM element to display debug output.

	progressDisplay - default: .upload-progress - selector for DOM element to target output container ( used to calculate meter constraints and any displayFields specified return data )

	progressMeter - default: .meter - selector for DOM element that will be horizontally resized against inner width of progressDisplay (minus 20 pixels padding) as upload progress changes. To disable meter updates, set this to false.
	
	targetUploader - default: jqUploader - id/name for upload target iframe.

	fieldPrefix - default: . (class selector) - selector prefix for jQuery DOM capture of displayField sub-elements of progressDisplay selector.

	displayFields - default (Array): ['est_sec'] - array of fields to parse from return ajax request data and target on to DOM elements prefixed by fieldPrefix.  See demo and example php servlet for details.
	
	start - default (Function): empty - function to run at beginning of submit request, prior to actual upload submit.
	
	success - default (Function): empty - function to run upon successful completion of upload
	
	failed - default (Function): empty - function to run if upload failed

global arrays:

several arrays are populated by upload key with data for interchange between routines at various stages of operation:

uploadProgressSettings - stores the final parameter settings
uploadProgressTimer - used for clearTimeout operation
uploadProgressNotFound - tick timer for 'upload not found' quirk
uploadProgressActive - used to manage several quirks
uploadProgressData - used to hold the last valid set of data
*/

var uploadProgressSettings = new Array();
var uploadProgressTimer = new Array();
var uploadProgressNotFound = new Array();
var uploadProgressActive = new Array();
var uploadProgressData = new Array();
var uploadCompletedData = new Array();

jQuery.fn.extend({

	uploadProgress: function(o) {
		var $id_field = jQuery('input[name="UPLOAD_IDENTIFIER"]',this);
		if (!o.id && $id_field.length)
			o.id = $id_field.val();
		if (!o.id)
			o.id = genUploadKey(o.keyLength);
		
		if ($id_field.length)
			$id_field.val(o.id);
		else
			jQuery('<input type="hidden" name="UPLOAD_IDENTIFIER"/>').val(o.id).prependTo(this);
		
		o = jQuery.extend({ dataFormat: 'json',
						    updateDelay: 1000,
							notFoundLimit: 5,
							debugDisplay: false,
							progressDisplay: '.upload-progress',
							progressMeter: '.meter',
							progressMeterSpeed: 500,
							targetUploader: 'jqUploader',
							fieldPrefix: '.',
							displayFields: ['est_sec'],
							start: function() {},
							success: function() {},
							failed: function() {} },o);
		
		uploadProgressSettings[o.id] = o;
		
		// iframe post files out: 
		jQuery(this).submit(function () {
			if (uploadProgressActive[o.id]) 
				return false;
			uploadProgressActive[o.id] = true;
			if (o.progressMeter)
				jQuery(o.progressMeter).animate({ 
					width: '0%'
				}, 100 );
			var theForm = this;
			o.start.call(theForm);

			jQuery(theForm).attr('target',o.targetUploader);

			var $upload_frame = jQuery('<iframe id="'+o.targetUploader+'" name="'+o.targetUploader+'"></iframe>');

			if (o.debugDisplay) {
				$('iframe#'+o.targetUploader).remove();
				$(o.debugDisplay).after($upload_frame);
			}
			else {
				$upload_frame.css({position:'absolute',top:'-500px',left:'-500px'}).appendTo('body');
			}

			$upload_frame.load(function() {
									clearTimeout(uploadProgressTimer[o.id]);
									if (o.progressMeter)
										jQuery(o.progressMeter).animate({ 
											width: '100%'
										  }, o.progressMeterSpeed );
									uploadCompletedData['filename'] = $upload_frame.contents().find('#filename').html();
									uploadCompletedData['type'] = $upload_frame.contents().find('#type').html();
									uploadCompletedData['error'] = $upload_frame.contents().find('#error').html();
									uploadCompletedData['size'] = $upload_frame.contents().find('#size').html();
									uploadCompletedData['timestamp'] = $upload_frame.contents().find('#timestamp').html();
									uploadCompletedData['icon'] = $upload_frame.contents().find('#icon').html();
									uploadCompletedData['ext'] = $upload_frame.contents().find('#ext').html();
									o.success.call(theForm, o);
									if (!o.debugDisplay)
										setTimeout(function() {	try { $upload_frame.remove();	} catch(e) { } }, 100);
									uploadProgressActive[o.id] = false;
								});
			uploadProgressTimer[o.id] = window.setTimeout("jQuery.uploadProgressUpdate('"+o.id+"')",o.updateDelay);
			uploadProgressNotFound[o.id] = 0;
			return true;
		} );

		return this;

		function genUploadKey(len) {
			if (!len) len = 11;
			var chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
			var key='';
			for (var i=0;i<len;i++) {
    			var charnum = Math.floor(Math.random()*(chars.length+1));
				key += chars.charAt(charnum);
			}
		    return key;
		}
	}
});

jQuery.extend({
	uploadProgressUpdate: function(id) {
		var o = uploadProgressSettings[id];
        var stamp = new Date().getTime();   // for IE request cache distinction   
		jQuery.ajax({url:o.progressURL, data:{'upload_id':id, 'stamp':stamp}, success: 
			function(data) {
				
				if (data['error']) {
					if (o.debugDisplay)
						jQuery(o.debugDisplay).append('<p>UP: '+data['error']+'</p>');
					uploadProgressNotFound[id]++;
					if (uploadProgressNotFound[id] >= o.notFoundLimit) {
						//uploadProgressActive[id] = false;
						o.failed.call();
						return false; // cancel timer renewal
					}
				}
				else {
					uploadProgressData[id] = data;
					if (o.debugDisplay) {
						var q='';
						for (var prop in data) {
							q += prop + ': '+data[prop]+'<br />';
						}
						jQuery(o.debugDisplay).html(q);
					}
					if (o.progressMeter) {
						var factor = Math.round((data['bytes_uploaded']/data['bytes_total'])*100);
						var factor2 = factor + "%";
						jQuery(o.progressMeter).animate({ 
							width: factor2
						  }, o.progressMeterSpeed );
					}
					for (var d = 0; d<o.displayFields.length; d++) {
						jQuery(o.fieldPrefix+o.displayFields[d], o.progressDisplay).html(data[o.displayFields[d]]);
					}
				}
				if (uploadProgressActive[id])
					uploadProgressTimer[id] = window.setTimeout("jQuery.uploadProgressUpdate('"+id+"')",o.updateDelay);
			}, dataType:o.dataFormat, error:function(xhr, err, et) {
				if (o.debugDisplay)
					jQuery(o.debugDisplay).append('<p>XHR: '+err+'</p>');
				o.failed.call();
				//uploadProgressActive[id] = false;
				return false;
			}
		});
	}
});
