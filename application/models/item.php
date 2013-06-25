<?php

class Item extends Model {
		var $hasMany = array('Product' => 'Product');
		var $hasOne = array('Parent' => 'Category');
}
