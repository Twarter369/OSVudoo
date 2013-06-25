function checkForFeaturedVids():void    
{    
    var f:Number = 0;  // create a variable for setting the featuredItem's name  
    var g:Number = 0;  // variable used to count the featured items and place them one after another
    var featuredItem = xml.GALLERY.CATEGORY.ITEM;    // variable that looks in the xml for all the items
    featuredContainer = new MovieClip();   
    featuredContainer.x = 25;    
    featuredContainer.y = 0;    
    featuredBox.addChild(featuredContainer);  // add the featuredContainer to the featuredBox    
    for each(var featuredVideoItem in xml.GALLERY.CATEGORY.ITEM.featured_or_not.*)  // loop through all the xml to find the featured_or_not line.
    {    
        if(featuredVideoItem == true)  // if the featured or not line is set to true then...
        {    
            var myFeaturedItem:featuredItem_mc = new featuredItem_mc();    
            myFeaturedItem.x = (myFeaturedItem.width + 2) * g;    
            myFeaturedItem.y = 0;    
            myFeaturedItem.buttonMode = true;    
            myFeaturedItem.mouseChildren = false;    
            myFeaturedItem.name = "" + f;    
            myFeaturedItem.addEventListener(MouseEvent.MOUSE_OVER, btnOver);    
            myFeaturedItem.addEventListener(MouseEvent.MOUSE_OUT, btnOut);    
            myFeaturedItem.featuredItemTitle_txt.text = xml.GALLERY.CATEGORY.ITEM.file_title.*[f];    
            myFeaturedItem.featuredItemDesc_txt.text = xml.GALLERY.CATEGORY.ITEM.file_desc.*[f];    
            featuredContainer.alpha = 0;    
            Tweener.addTween(featuredContainer, {alpha:1, time:.5, transition:"easeOut"});               
            var featuredThumbURL = xml.GALLERY.CATEGORY.ITEM.featured_image.*[f];    
            featuredThumbLoader = new Loader();    
            featuredThumbLoader.load(new URLRequest(featuredThumbURL));    
            featuredThumbLoader.x = 4;    
            featuredThumbLoader.y = 4;    
            myFeaturedItem.addChildAt(featuredThumbLoader, 5);    
            featuredThumbLoader.contentLoaderInfo.addEventListener(Event.COMPLETE, featuredThumbLoaded);    
            featuredContainer.addChild(myFeaturedItem);    
            g++;    
        }    
        f++;    
    }   
    checkFeaturedContainerWidth(); 
}    
function featuredThumbLoaded(event:Event):void    
{    
    var featuredPreloader = event.target.loader.parent.featuredItemPreloader_mc;    
    Tweener.addTween(featuredPreloader, {alpha:0, time:.5, transition:"easeOut"});    
}