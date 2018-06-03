# DevElation

Overview
===

It was a web library I started back in 2004 and it did a lot to teach me how to make things efficient, but as you can imagine over 15 or so years it's been deprecated. Also, it was a startup when I built this (I like startups) so it was all super rushed and "just get it working before money runs out" and stuff.

Common Functions
---
- `dev_control_var('global_variable_name');` // get the value of a global or request variable matching the string passed and returns the value
- `dev_set_cookie('name', 'value', ['timeout']);` // Sets a cookie set the second value to an empty string to delete
- `dev_dbconn('db_user', 'db_name', 'db_password', 'db_host'); // opens a php.mysql connection
- `dev_dbclose([$link]);` // closes a connection
- `dev_href([$url]);` // formats the url passed, or returns the url of the page if no value is given
- `dev_draw_form($action_href);` // returns the open tag html of a POST form
- `dev_draw_form_field('field_type', 'field_name', 'field_label', 'field_value');` // returns an html form field
- `dev_close_form();` // closing tags of an html form
- `dev_redirect($href);` // http redirect of a page so long as no headers have been sent to the browser yet

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
- `$variable->delete();` // deletes all records matching the set fields
- `$variable->read();` // Selects the first record matching the set fields
- `$variable->clearMembers();` // clears all fields to default value ( same as DevObject::clear() )
- `$variable->getRecordSet();` // runs a select query for all rows matching the set fields and conditions
- `$variable->getStatusMessage();` // last database status or update success/error message
- `$variable->drawForm([$type_array], [$header_array]);` // Generates `dev_draw_form_field()` fields for each database column. $type_array is an associative array of field types, $header_array contains field labels. Both optional

DevTemplate
---
This is the template class I developed. 
`$template = new DevTemplate('file_location.html');`
This code creates a variable '$template' which loads a plain text file with replaceable tokens. For Example, if the file loaded was index.html and had a line like 
`<title>{page_title}</title>
the following code 
`$template = new DevTemplate('index.html');
$template->set('page_title', 'Hello, World');
$template->publish();`
Would produce the output
`<title>Hello, World</title>`

- `$template->render();` // returns the formatted template file
- `$template->publish();` // renders the template then echos it
- `$template->set('token', $value);` // Immediately updates the token {token} in the template file 
- `$template->assign('token', $value);` // Queues the token {token} in the template file to be update to $value when DevTemplate::render() is called
- `$template->renderRecordSet($recordset);` // iterates the template file over each set of values in an associative array
- `$template->renderRecordSetFormatted($recordset);` // same as DevTemplate::renderRecordSet but escapes strings and process htmlentities
