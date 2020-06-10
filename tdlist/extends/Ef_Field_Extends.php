<?php

// Specialized to add bootstrap behaviour
class Ef_Field extends F_Field 
{

	// Add label to a field value of a field  - bootstrap mode
    public function addLabel($fieldname, $stringvalue)
    {
        // $fieldlabel = F_Lang::get($fieldname);
        $fieldlabel = Ef_Lang::get($this->getName());
        
        return "$fieldlabel $stringvalue";
        $labeledstring = ("  
            <div class=\"form-group\">
            <label for=\"$fieldname\" class=\"col-sm-3 control-label\">$fieldlabel</label>
            $stringvalue        
            </div>            
        ");
        return $labeledstring;
    }

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