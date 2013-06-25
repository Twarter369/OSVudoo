function makeVideoItems():void    
{    
    xCount = 0;
    yCount = 0;
    container_mc = new MovieClip();
    container_mc.x = 2;
    container_mc.y = 60;
    sidebarBox.addChild(container_mc);
	if(a > 0)
    {    
        a = 0;
    }    
	for each(var videoNode:XML in xml.GALLERY[currentGallery].CATEGORY[categoryItemName].ITEM)
	{    
	    var videoItem:videoItem_mc = new videoItem_mc;
	    videoItem.videoItemTitle_txt.text = videoNode.file_title;
	    videoItem.videoItemDesc_txt.text = videoNode.file_desc;
	    videoItem.x = (videoItem.width + 3) * xCount;
	    videoItem.y = (videoItem.height + 3) * yCount;  
	    videoItem.buttonMode = true;    
	    videoItem.mouseChildren = false;    
	    videoItem.name = "" + a;
	    videoItem.addEventListener(MouseEvent.MOUSE_OVER, btnOver);    
	    videoItem.addEventListener(MouseEvent.MOUSE_OUT, btnOut);    
	    container_mc.alpha = 0;    
	   	Tweener.addTween(container_mc, {alpha:1, time:.5, transition:"easeOut"});    
	   	var videoThumbURL = videoNode.file_image;
	    videoThumbLoader = new Loader();
	    videoThumbLoader.load(new URLRequest(videoThumbURL));
	    videoThumbLoader.x = 3;  
	    videoThumbLoader.y = 3;    
	    videoItem.addChild(videoThumbLoader); 
	    videoThumbLoader.contentLoaderInfo.addEventListener(Event.COMPLETE, videoThumbLoaded);   
	    container_mc.addChild(videoItem);
	    a++;
	    if(xCount + 1 < columns)
   		{    
    	    xCount++;
   		}    
   		else
   		{    
   		    xCount = 0;
   		    yCount++;
    	}    
	}
	checkContainerHeight();
}    