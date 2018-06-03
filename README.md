#DevElation

Overview
===

It was a web library I started back in 2004 and it did a lot to teach me how to make things efficient, but as you can imagine over 15 or so years it's been deprecated. Also, it was a startup when I built this (I like startups) so it was all super rushed and "just get it working before money runs out" and stuff.

DevObject
--- 
This is the database class I developed. It was TOTALLY BOSS until the php.mysql extension was deprecated. It was also crazy hard to maintain. 

`$variable = new DevObject('database_table_name');`
The code above makes `$variable` an object that can run queries against the table `'database_table_name'` The most common operations I created are

- `$variable->loadPostVars();` // Loads variables from global variables ($_POST)
- `$variable->setField('database_field', $value);` // sets a value to the database field. Not set until saved (write)
- `$variable->getField('database_field');` // gets the value currently assigned to the field
- `$variable->setCondition('database_field', '=', $value);` // like DevObject::setField() but with conditional logic. Second argument can be =,>,<,<>, or LIKE
- `$variable->writeObject();` // creates or updates the object(s) to the database ( same as DevObject::save() )
- `$variable->clearMembers();` // clears all fields to default value ( same as DevObject::clear() )
- `$variable->getRecordSet();` // runs a select query for all rows matching the set fields and conditions
- `$variable->getStatusMessage();` // last database status or update success/error message

DevTemplate
---
This is the template class I developed. 
`$template = new DevTemplate('file_location.html');`
This code creates a variable $template which 
