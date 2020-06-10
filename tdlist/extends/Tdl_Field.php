<?php

// New field type : check button, based on integer
class Ef_FieldChecked extends Ef_FieldInt 
{

    private $submitonchange;
    
	public function __construct($argname, $argattrib) 
    {
		parent::__construct($argname,$argattrib);
        if (isset($argattrib['submitonchange'])) {
            $this->submitonchange = true;
        }            		
	}	
    
    public function memToEditHtml ($value, $parms=array()) 
    {
        $checked = '';
        if ($value == 1) {
            $checked = 'checked';
        }
    	$editname = $this->getEditname ($parms);
	    $fieldvalue = htmlspecialchars($value);
        $fieldlabel = Ef_Lang::get($this->getName());
        
        if ($this->submitonchange) {
            $submitit = 'onchange="document.forms[0].submit()"';
        } else {
            $submitit = '';
        }

        // notice that bootstrap class "form-control" makes a very big check box         
        $classtext = '';         
        
        $inputfield = ("        
            <div class=\"form-group\"  
                <label for=\"$editname\"> $fieldlabel </label> 
                <input type=\"checkbox\" id=\"$editname\" name=\"$editname\" $classtext $submitit $checked> 
            </div>
             ");
             
        return $inputfield;
    }

	public function memToViewHtml ($value, $parms=array()) 
    {
        return parent::memToViewHtml ($value, $parms);
    }    
        
	public function postHtmlToMem($value='', $parms=array()) 
    {    
        if ($value) 
            return 1;
        else 
            return 0;
	}    
}




// Specialized to add bootstrap behaviour
class Ef_FieldString extends F_FieldString 
{

    // Add placeholder and class, bootstrap way
    public function memToEditHtml ($value, $parms=array())
    {
        $editField = parent::memToEditHtml($value, $parms);
        $fieldlabel = Ef_Lang::get($this->getName()); 

        $placeholdertext = "placeholder=\"$fieldlabel\"";        
        $classtext = 'class="form-control"';         
        
        return (str_replace('data-dummy="dummy"', $placeholdertext.' '.$classtext, $editField));
    
    }

}

// Specialized to add bootstrap behaviour
class Ef_FieldRowButton extends F_FieldRowButton 
{

    // Add class, bootstrap way
    public function memToEditHtml ($value, $parms=array())
    {
        $editField = parent::memToEditHtml($value, $parms);
        $fieldlabel = Ef_Lang::get($this->getName()); 

        $classtext = 'class="btn"';  // to see btn-default btn-primary         
        
        return (str_replace('data-dummy="dummy"', $classtext, $editField));
    
    }

}



// Specialized to add bootstrap behaviour
class Ef_FieldInt extends F_FieldInt 
{

    // Add placeholder and class, bootstrap way
    public function memToEditHtml ($value, $parms=array())
    {
        $editField = parent::memToEditHtml($value, $parms);
        $fieldlabel = Ef_Lang::get($this->getName()); 

        $placeholdertext = "placeholder=\"$fieldlabel\"";        
        $classtext = 'class="form-control"';         
        
        return (str_replace('data-dummy="dummy"', $placeholdertext.' '.$classtext, $editField));
    
    }
}

// Specialized to add bootstrap behaviour
class Ef_FieldAmount extends F_FieldAmount 
{

    // Add placeholder and class, bootstrap way
    public function memToEditHtml ($value, $parms=array())
    {
        $editField = parent::memToEditHtml($value, $parms);
        $fieldlabel = Ef_Lang::get($this->getName()); 

        $placeholdertext = "placeholder=\"$fieldlabel\"";        
        $classtext = 'class="form-control"';         
        
        return (str_replace('data-dummy="dummy"', $placeholdertext.' '.$classtext, $editField));
    
    }
}

// Specialized to add bootstrap behaviour
class Ef_FieldSelect extends F_FieldSelect 
{

    // Add placeholder and class, bootstrap way
    public function memToEditHtml ($value, $parms=array())
    {
        $editField = parent::memToEditHtml($value, $parms);
        $fieldlabel = Ef_Lang::get($this->getName()); 

        $placeholdertext = "placeholder=\"$fieldlabel\"";        
        $classtext = 'class="form-control"';         
        
        return (str_replace('data-dummy="dummy"', $placeholdertext.' '.$classtext, $editField));
    
    }
}

// Specialized to add bootstrap behaviour
class Ef_FieldDate extends F_FieldDate 
{

    // Add placeholder and class, bootstrap way
    public function memToEditHtml ($value, $parms=array())
    {
        $editField = parent::memToEditHtml($value, $parms);
        $fieldlabel = Ef_Lang::get($this->getName()); 

        $placeholdertext = "placeholder=\"$fieldlabel\"";        
        $classtext = 'class="form-control"';         
        
        return (str_replace('data-dummy="dummy"', $placeholdertext.' '.$classtext, $editField));
    
    }
}

// Specialized to add bootstrap behaviour
class Ef_FieldText extends F_FieldText 
{

    // Add placeholder and class, bootstrap way
    public function memToEditHtml ($value, $parms=array())
    {
        $editField = parent::memToEditHtml($value, $parms);
        $fieldlabel = Ef_Lang::get($this->getName()); 

        $placeholdertext = "placeholder=\"$fieldlabel\"";        
        $classtext = 'class="form-control"';         
        
        return (str_replace('data-dummy="dummy"', $placeholdertext.' '.$classtext, $editField));
    
    }
}


?>