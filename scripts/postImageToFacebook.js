//
// Code freely provided by 'danguer': https://github.com/danguer/blog-examples/blob/master/js/base64-binary.js
//

var Base64Binary = {
	_keyStr : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",
	
	/* will return a  Uint8Array type */
	decodeArrayBuffer: function(input) {
		var bytes = (input.length/4) * 3;
		var ab = new ArrayBuffer(bytes);
		this.decode(input, ab);
		
		return ab;
	},

	removePaddingChars: function(input){
		var lkey = this._keyStr.indexOf(input.charAt(input.length - 1));
		if(lkey == 64){
			return input.substring(0,input.length - 1);
		}
		return input;
	},

	decode: function (input, arrayBuffer) {
		//get last chars to see if are valid
		input = this.removePaddingChars(input);
		input = this.removePaddingChars(input);

		var bytes = parseInt((input.length / 4) * 3, 10);
		
		var uarray;
		var chr1, chr2, chr3;
		var enc1, enc2, enc3, enc4;
		var i = 0;
		var j = 0;
		
		if (arrayBuffer)
			uarray = new Uint8Array(arrayBuffer);
		else
			uarray = new Uint8Array(bytes);
		
		input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");
		
		for (i=0; i<bytes; i+=3) {	
			//get the 3 octects in 4 ascii chars
			enc1 = this._keyStr.indexOf(input.charAt(j++));
			enc2 = this._keyStr.indexOf(input.charAt(j++));
			enc3 = this._keyStr.indexOf(input.charAt(j++));
			enc4 = this._keyStr.indexOf(input.charAt(j++));
	
			chr1 = (enc1 << 2) | (enc2 >> 4);
			chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
			chr3 = ((enc3 & 3) << 6) | enc4;
	
			uarray[i] = chr1;			
			if (enc3 != 64) uarray[i+1] = chr2;
			if (enc4 != 64) uarray[i+2] = chr3;
		}
	
		return uarray;	
	}
}

//
// Code provided freely by 'andyburke': https://gist.github.com/andyburke/1498758, but modified slightly by Phillip Nazarian
//

//
// Post an image specified as bytes to Facebook
//
// @param authToken: the Facebook authToken to use
// @param filename: the filename to use
// @param mimeType: the MIME type of the image data
// @param caption: a caption to accompany the image
// @param imageData: image data as bytes
//

function postImageToFacebook(authToken, filename, mimeType, caption, imageData)
{
		if ( XMLHttpRequest.prototype.sendAsBinary === undefined ) {
			XMLHttpRequest.prototype.sendAsBinary = function(string) {
				var bytes = Array.prototype.map.call(string, function(c) {
					return c.charCodeAt(0) & 0xff;
				});
				this.send(new Uint8Array(bytes).buffer);
			};
		}		
		
		// this is the multipart/form-data boundary we'll use
		var boundary = '----ThisIsTheBoundary1234567890';
		
		// let's encode our image file, which is contained in the var
		var formData = '--' + boundary + '\r\n'
		formData += 'Content-Disposition: form-data; name="source"; filename="' + filename + '"\r\n';
		formData += 'Content-Type: ' + mimeType + '\r\n\r\n';
		for ( var i = 0; i < imageData.length; ++i )
		{
			formData += String.fromCharCode( imageData[ i ] & 0xff );
		}
		formData += '\r\n';
		formData += '--' + boundary + '\r\n';
		formData += 'Content-Disposition: form-data; name="caption"\r\n\r\n';
		formData += caption + '\r\n'
		formData += '--' + boundary + '--\r\n';
		
		var xhr = new XMLHttpRequest();
		xhr.open( 'POST', 'https://graph.facebook.com/me/photos?access_token=' + authToken, true );
		xhr.onload = xhr.onerror = function() {
			//console.log( xhr.responseText );
		};
		xhr.setRequestHeader( "Content-Type", "multipart/form-data; boundary=" + boundary );
		xhr.sendAsBinary( formData );
}