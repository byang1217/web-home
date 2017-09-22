function toggle_checkbox_with_img(checkbox_id, img_id, img_src_y, img_src_n )
{
	var checkbox_obj = document.getElementById(checkbox_id);
	var img_obj = document.getElementById(img_id);

	if (checkbox_obj.checked) {
		checkbox_obj.checked = false;
		img_obj.src = img_src_n;
	}else {
		checkbox_obj.checked = true;
		img_obj.src = img_src_y;
	}
}

function view_toggle(id)
{
        var obj = document.getElementById(id);
        if (!obj) return false;

        if (obj.style.display == "none" || obj.style.display == 'none')
                obj.style.display = "block";
        else
                obj.style.display = "none";
}

function set_value_by_id(id, v){
	t = document.getElementById(id)
	t.value = v
}

function img_rotate(obj,arr){
	var img = document.getElementById(obj);
	if(!img || !arr) return false;
	var n = img.getAttribute('step');
	if(n== null) n=0;
	if(arr=='left'){
		(n==0)? n=3:n--;
	}else if(arr=='right'){
		(n==3)? n=0:n++;
	}else {
		n=arr;
	}
	img.setAttribute('step',n);

	var c = document.getElementById('canvas_'+obj);
	if(c== null){
		img.style.visibility = 'hidden';
		img.style.position = 'absolute';
		c = document.createElement('canvas');
		c.setAttribute("id",'canvas_'+obj);
		img.parentNode.appendChild(c);
	}
	var canvasContext = c.getContext('2d');
	if (img.height < img.width) {
		resize = 1;
	}else {
		resize = img.height / img.width;
	}
	switch(n) {
		default :
		case 0 :
			c.setAttribute('width', img.width);
			c.setAttribute('height', img.height);
			canvasContext.rotate(0 * Math.PI / 180);
			canvasContext.drawImage(img, 0, 0, img.width, img.height);
			break;
		case 1 :
			c.setAttribute('width', img.height/resize);
			c.setAttribute('height', img.width/resize);
			canvasContext.rotate(90 * Math.PI / 180);
			canvasContext.drawImage(img, 0, -img.height/resize, img.width/resize, img.height/resize);
			break;
		case 2 :
			//180
			c.setAttribute('width', img.width);
			c.setAttribute('height', img.height);
			canvasContext.rotate(180 * Math.PI / 180);
			canvasContext.drawImage(img, -img.width, -img.height, img.width, img.height);
			break;
		case 3 :
			c.setAttribute('width', img.height/resize);
			c.setAttribute('height', img.width/resize);
			canvasContext.rotate(270 * Math.PI / 180);
			canvasContext.drawImage(img, -img.width/resize, 0, img.width/resize, img.height/resize);
			break;
	};
}

