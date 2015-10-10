<?php
	/**
	 * CI generator v 0.0.1
	 *
	 * @author  Manuel Avalos <manuelandresavalos@gmail.com>
	 */

	class CI_generator {
		/**
		 * $ci var is used to load codeigniter object
		 * @var [Object]
		 */
		public $ci;

		/**
		 * [$base_path description]
		 * @var [type]
		 */
		private $base_path = __DIR__;

		/**
		 * This var is used to set the name of models folder into into application/third_party/ci_generator/ path
		 * @var [String]
		 */
		public $base_export_folder = 'exported_clases';

		/**
		 * This var is used to set the name of models folder into into application/third_party/ci_generator/ path
		 * @var [String]
		 */
		public $models_exported_folder = 'models';

		/**
		 * This var is used to set the name of controllers folder into into application/third_party/ci_generator/ path
		 * @var [String]
		 */
		public $controllers_exported_folder = 'controllers';

		/**
		 * [$prefix_model description]
		 * @var string
		 */
		public $prefix_model = 'CI_';

		/**
		 * [$prefix_controller description]
		 * @var string
		 */
		public $prefix_controller = 'CI_';

		/**
	     * Execute the construct of the clase
	     * @param Object $codeigniter The Codeigniter Object
	     */
		public function __construct($codeigniter)
		{
			//Referencing codeigniter object
			$this->ci = $codeigniter;

			//Loading database library
			$this->ci->load->database();

			//Folders creation
			$this->_create_export_folder();
			$this->_create_models_folder();
			$this->_create_controllers_folder();
			//End Folders creation
		}

		/**
		 * [generate_models description]
		 * @return [type] [description]
		 */
		public function generate()
		{
			$sql_tables_names = "SELECT table_name FROM information_schema.tables WHERE table_schema='" . $this->ci->db->database . "'";
			$query_sql_tables_names = $this->ci->db->query($sql_tables_names);
			$this->tables_names_object = $query_sql_tables_names->result();
			/**
			 * $query_sql_tables_names->result();
			 * Return a list of tables name from database
			 * Array
			 * (
			 *	    [0] => stdClass Object
			 *	        (
			 *	            [table_name] => facturas
			 *	        )
			 * )
			 */

			foreach ($this->tables_names_object as $row)
			{
				$table_name = $row->table_name;
				/**
				 * $result_sql_columns_names->result();
				 * Return a list of fields from each table and there attrubutes.
				 * Array (
			     * [0] => stdClass Object
			     *     (
			     *         [column_name] => id_facturas
			     *         [column_key] => PRI
			     *         [extra] => auto_increment
			     *     )
			     * )
				 */
				$sql_columns_names = "SELECT column_name, column_key, extra FROM information_schema.columns WHERE table_schema='" . $this->ci->db->database . "' AND table_name='$table_name'";
				$result_sql_columns_names = $this->ci->db->query($sql_columns_names);
				$columns_names_array = $result_sql_columns_names->result_array();

				//-----------------------------------------------------------------------------//
				//Generate MODELS:
				//-----------------------------------------------------------------------------//
				//Open or create the model file
				$model_file = fopen( $this->base_path . '/'
					. $this->base_export_folder . '/'
					. $this->models_exported_folder . '/'
					. strtolower($table_name)
					. "_model.php", "w");

				//Create the string for the entire class
				$string_result = $this->model_string_init($table_name);
				$string_result .= $this->model_string_create_method($table_name, $columns_names_array);
				$string_result .= $this->model_string_update_method($table_name, $columns_names_array);
				$string_result .= $this->model_string_delete_method($table_name);
				$string_result .= $this->model_string_get_by_id_method($table_name, $columns_names_array);
				$string_result .= $this->model_string_get_all_method($table_name);
				$string_result .= $this->model_string_end_class();

				//Write the model file
				fwrite($model_file, $string_result);

				//-----------------------------------------------------------------------------//
				//Generate CONTROLLERS:
				//-----------------------------------------------------------------------------//
				//Open or create the model file
				$controller_file = fopen( $this->base_path . '/'
					. $this->base_export_folder . '/'
					. $this->controllers_exported_folder . '/'
					. strtolower($table_name)
					. ".php", "w");

				//Create the string for the entire class
				$string_result = $this->controller_string_init($table_name);
				$string_result .= $this->controller_string_index_method($table_name, $columns_names_array);
				$string_result .= $this->controller_string_create_method($table_name, $columns_names_array);
				$string_result .= $this->controller_string_update_method($table_name, $columns_names_array);
				$string_result .= $this->controller_string_delete_method($table_name);
				$string_result .= $this->controller_string_get_by_id_method($table_name, $columns_names_array);
				$string_result .= $this->controller_string_get_all_method($table_name);
				$string_result .= $this->controller_string_end_class();

				//Write the model file
				fwrite($controller_file, $string_result);
			}
			/**/
			echo "Generated models code!";
		}

		/**
		 * [_string_init_class description]
		 * @param  [type] $table_name [description]
		 * @return [type]             [description]
		 */
		private function model_string_init($table_name)
		{
			$content_file = '<?php'																					. PHP_EOL;
			$content_file .= '	defined(\'BASEPATH\') OR exit(\'No direct script access allowed\');'				. PHP_EOL;
			$content_file .= '	class ' . ucfirst($table_name) . '_model extends ' . $this->prefix_model . 'Model' 	. PHP_EOL;
			$content_file .=' 	{'																					. PHP_EOL;
			$content_file .=''																 						. PHP_EOL;
			$content_file .=' 		public function __construct()'															. PHP_EOL;
			$content_file .=' 		{'																				. PHP_EOL;
			$content_file .=' 			parent::__construct();'														. PHP_EOL;
			$content_file .=' 			$this->load->database();'													. PHP_EOL;
			$content_file .=' 		}'																				. PHP_EOL;
			$content_file .=''																						. PHP_EOL;

			return $content_file;
		}

		/**
		 * [model_string_create_method_class description]
		 * @param  [type] $table_name          [description]
		 * @param  [type] $columns_names_array [description]
		 * @return [type]                      [description]
		 */
		private function model_string_create_method($table_name, $columns_names_array)
		{
			$content_file =' 		public function create($item)'										. PHP_EOL;
			$content_file .=' 		{'															. PHP_EOL;
			$content_file .=' 			$data = array('											. PHP_EOL;
			/**
			 * $this->_get_attributes_from_table() Return a list of fields in string format.
			 */
			$content_file .= $this->_get_attributes_from_table($columns_names_array);
			$content_file .=' 			);'														. PHP_EOL;
			$content_file .=''																 	. PHP_EOL;
			$content_file .=' 			$this->db->insert(\''. strtolower($table_name) .'\', $data);'. PHP_EOL;
			$content_file .=' 		}'															. PHP_EOL;
			$content_file .=''																 	. PHP_EOL;

			return $content_file;
		}

		/**
		 * [model_string_get_by_id_method description]
		 * @param  [type] $table_name          [description]
		 * @param  [type] $columns_names_array [description]
		 * @return [type]                      [description]
		 */
		private function model_string_get_by_id_method($table_name, $columns_names_array)
		{
			$content_file =' 		public function get_by_id($id_' . strtolower($table_name) . ')'									. PHP_EOL;
			$content_file .=' 		{'															. PHP_EOL;
			$content_file .=' 			$this->db->select(\'*\');'								. PHP_EOL;
			$content_file .=' 			$this->db->from(\'' . strtolower($table_name) . '\');'	. PHP_EOL;

			foreach ($columns_names_array as $row_column)
			{
				if($row_column["column_key"] == "PRI" && $row_column["extra"] == "auto_increment")
				{
					$content_file .=' 			$this->db->where(\'' . $row_column['column_name'] . '\', $id_' . strtolower($table_name) . ');'					. PHP_EOL;
				}
			}

			$content_file .=' 			$query = $this->db->get();'								. PHP_EOL;
			$content_file .=''																 	. PHP_EOL;
			$content_file .=' 			if($query->num_rows()<1){'								. PHP_EOL;
			$content_file .=' 				return null;'										. PHP_EOL;
			$content_file .=' 			}'														. PHP_EOL;
			$content_file .=' 			else{'													. PHP_EOL;
			$content_file .=' 				return $query->result_array();'						. PHP_EOL;
			$content_file .=' 			}'														. PHP_EOL;
			$content_file .=' 		}'															. PHP_EOL;
			$content_file .=''																 	. PHP_EOL;

			return $content_file;
		}

		/**
		 * [model_string_get_all_method description]
		 * @param  [type] $table_name [description]
		 * @return [type]             [description]
		 */
		private function model_string_get_all_method($table_name)
		{
			$content_file =' 		public function get_all()'											. PHP_EOL;
			$content_file .=' 		{'															. PHP_EOL;
			$content_file .=' 			$this->db->select(\'*\');'								. PHP_EOL;
			$content_file .=' 			$this->db->from(\'' . strtolower($table_name) . '\');'	. PHP_EOL;
			$content_file .=' 			$query = $this->db->get();'								. PHP_EOL;
			$content_file .=''																 	. PHP_EOL;
			$content_file .=' 			if($query->num_rows()<1){'								. PHP_EOL;
			$content_file .=' 				return null;'										. PHP_EOL;
			$content_file .=' 			}'														. PHP_EOL;
			$content_file .=' 			else{'													. PHP_EOL;
			$content_file .=' 				return $query->result_array();'						. PHP_EOL;
			$content_file .=' 			}'														. PHP_EOL;
			$content_file .=' 		}'															. PHP_EOL;
			$content_file .=''																 	. PHP_EOL;

			return $content_file;
		}

		/**
		 * [model_string_update_method description]
		 * @param  [type] $table_name          [description]
		 * @param  [type] $columns_names_array [description]
		 * @return [type]                      [description]
		 */
		private function model_string_update_method($table_name, $columns_names_array)
		{
			$content_file =' 		public function update($id_' . strtolower($table_name) . ', $item)'. PHP_EOL;
			$content_file .=' 		{'															. PHP_EOL;
			$content_file .=' 			$data = array('											. PHP_EOL;
			$content_file .= $this->_get_attributes_from_table($columns_names_array);
			$content_file .=' 			);'														. PHP_EOL;
			$content_file .=''																 	. PHP_EOL;
			$content_file .=' 			$this->db->where(\'id_' . strtolower($table_name) . '\', $id_' . strtolower($table_name) . ');'							. PHP_EOL;
			$content_file .=' 			$this->db->update(\'' . strtolower($table_name) . '\', $data);'					. PHP_EOL;
			$content_file .=' 		}'															. PHP_EOL;
			$content_file .=''																 	. PHP_EOL;

			return $content_file;
		}

		/**
		 * [model_string_delete_method description]
		 * @param  [type] $table_name [description]
		 * @return [type]             [description]
		 */
		private function model_string_delete_method($table_name)
		{
			$content_file =' 		public function delete($id_' . strtolower($table_name) . ')'		. PHP_EOL;
			$content_file .=' 		{'															. PHP_EOL;
			$content_file .=' 			$this->db->where(\'id_' . strtolower($table_name) . '\', $id_' . strtolower($table_name) . ');'							. PHP_EOL;
			$content_file .=' 			$this->db->delete(\'' . strtolower($table_name) . '\');'. PHP_EOL;
			$content_file .=' 		}'															. PHP_EOL;
			$content_file .=''																						. PHP_EOL;

			return $content_file;
		}

		/**
		 * [model_string_end_class description]
		 * @return [type] [description]
		 */
		private function model_string_end_class()
		{
			$content_file =' 	}'																. PHP_EOL;

			return $content_file;
		}


		/**
		 * [controller_string_init_class description]
		 * @param  [type] $table_name   [description]
		 * @param  string $prefix_model [description]
		 * @return [type]               [description]
		 */
		private function controller_string_init($table_name)
		{
			$content_file = '<?php' 																					. PHP_EOL;
			$content_file .= '	defined(\'BASEPATH\') OR exit(\'No direct script access allowed\');'						. PHP_EOL;
			$content_file .= '	class ' . ucfirst($table_name) . ' extends ' . $this->prefix_controller . 'Controller' 	. PHP_EOL;
			$content_file .= ' 	{'																						. PHP_EOL;
			$content_file .= ''																							. PHP_EOL;
			$content_file .= ' 		function __construct()'																. PHP_EOL;
			$content_file .= ' 		{'																					. PHP_EOL;
			$content_file .= ' 			parent::__construct();'															. PHP_EOL;
			$content_file .= '			$this->load->model(\''. strtolower($table_name) .'_model\');' 					. PHP_EOL;
			$content_file .= ' 		}'																					. PHP_EOL;
			$content_file .= ''																							. PHP_EOL;

			return $content_file;
		}

		/**
		 * [controller_string_index_method description]
		 * @param  [type] $table_name          [description]
		 * @param  [type] $columns_names_array [description]
		 * @return [type]                      [description]
		 */
		private function controller_string_index_method($table_name, $columns_names_array)
		{
			$content_file = '		public function index()' 												. PHP_EOL;
			$content_file .= '		{'																		. PHP_EOL;
			$content_file .= '			$view = \''. strtolower($table_name) .'\';'							. PHP_EOL;
			$content_file .= '			$this->load->view(\''. strtolower($table_name) .'\');'				. PHP_EOL;
			$content_file .= '		}'																		. PHP_EOL;
			$content_file .= ''																				. PHP_EOL;

			return $content_file;
		}

		/**
		 * [controller_string_create_method description]
		 * @param  [type] $table_name          [description]
		 * @param  [type] $columns_names_array [description]
		 * @return [type]                      [description]
		 */
		public function controller_string_create_method($table_name, $columns_names_array)
		{
			$content_file = ' 		public function create(' . $this->_get_update_fields_inline($columns_names_array) . ')' . PHP_EOL;
			$content_file .= ' 		{'																			. PHP_EOL;
			$content_file .= 			$this->_get_update_fields_for_input($columns_names_array);
			$content_file .= ''																					. PHP_EOL;
			$content_file .= ' 			$result = $this->db->insert(\'' . strtolower($table_name) . '\', $item);'									. PHP_EOL;
			$content_file .= ' 			$data[\'message\'] = ($result) ? \'Creado!\' : \'mmm algo salio mal, no se pudo crear...\';';
			$content_file .= ''																			 		. PHP_EOL;
			$content_file .= '			$this->load->view(\'' . strtolower($table_name) . '\', $data);'			. PHP_EOL;
			$content_file .= ' 		}'																			. PHP_EOL;
			$content_file .= ''																					. PHP_EOL;

			return $content_file;
		}

		/**
		 * [controller_string_get_by_id_method description]
		 * @param  [type] $table_name          [description]
		 * @param  [type] $columns_names_array [description]
		 * @return [type]                      [description]
		 */
		private function controller_string_get_by_id_method($table_name, $columns_names_array)
		{
			$content_file = ' 		public function get_by_id($id_' . strtolower($table_name) . ')'				. PHP_EOL;
			$content_file .= ' 		{'																			. PHP_EOL;
			$content_file .= ' 			$data[\'' . strtolower($table_name) . '\'] = $this->' . strtolower($table_name) . '_model->get_by_id($id_' . strtolower($table_name) . ');'. PHP_EOL;
			$content_file .= '			$this->load->view(\'' . strtolower($table_name) . '\', $data);' 		. PHP_EOL;
			//$content_file .= '			$this->uri_autoformat_view($data, $view);' 							. PHP_EOL;
			$content_file .= '		}'																		 	. PHP_EOL;
			$content_file .= ''																				 	. PHP_EOL;

			return $content_file;
		}

		/**
		 * [controller_string_get_all_method description]
		 * @param  [type] $table_name [description]
		 * @return [type]             [description]
		 */
		private function controller_string_get_all_method($table_name)
		{
			$content_file = ' 		public function get_all()'													. PHP_EOL;
			$content_file .= ' 		{'																			. PHP_EOL;
			$content_file .= ' 			$data[\'' . strtolower($table_name) . '\'] = $this->' . strtolower($table_name) . '_model->get_all();'. PHP_EOL;
			$content_file .= '			$this->load->view(\'' . strtolower($table_name) . '\', $data);' 		. PHP_EOL;
			//$content_file .= '			$this->uri_autoformat_view($data, $view);' 							. PHP_EOL;
			$content_file .= '		}'																		 	. PHP_EOL;
			$content_file .= ''																				 	. PHP_EOL;

			return $content_file;
		}

		/**
		 * [controller_string_update_method description]
		 * @param  [type] $table_name          [description]
		 * @param  [type] $columns_names_array [description]
		 * @return [type]                      [description]
		 */
		private function controller_string_update_method($table_name, $columns_names_array)
		{
			$content_file = ' 		public function update(' . $this->_get_update_fields_inline($columns_names_array) . ')'													. PHP_EOL;
			$content_file .= ' 		{'																			. PHP_EOL;
			$content_file .= 			$this->_get_update_fields_for_input($columns_names_array);
			$content_file .= '			$result = $this->$this->usuarios_model->update(\'id_' . strtolower($table_name) . '\', $item);'	. PHP_EOL;
			$content_file .= ''																			 		. PHP_EOL;
			$content_file .= ' 			$result = $this->db->insert(\'' . strtolower($table_name) . '\', $item);'									. PHP_EOL;
			$content_file .= ' 			$data[\'message\'] = ($result) ? \'Actualizado!\' : \'mmm algo salio mal, no se pudo actualizar...\';';
			$content_file .= ''																			 		. PHP_EOL;
			$content_file .= '			$this->load->view(\'' . strtolower($table_name) . '\', $data);'			. PHP_EOL;
			//$content_file .= '			$this->uri_autoformat_view($data, $view);' 							. PHP_EOL;
			$content_file .= '		}'																		 	. PHP_EOL;
			$content_file .= ''																				 	. PHP_EOL;

			return $content_file;
		}

		/**
		 * [controller_string_delete_method description]
		 * @param  [type] $table_name [description]
		 * @return [type]             [description]
		 */
		private function controller_string_delete_method($table_name)
		{
			$content_file = ' 		public function delete($id_' . strtolower($table_name) . ')'	. PHP_EOL;
	 		$content_file .= '		{' 																. PHP_EOL;
	 		$content_file .= '			$this->db->where(\'id_' . strtolower($table_name) . '\', $id_' . strtolower($table_name) . ');' . PHP_EOL;
	 		$content_file .= ''																			 		. PHP_EOL;
	 		$content_file .= '			$result = $this->db->delete(\'' . strtolower($table_name) . '\');'	. PHP_EOL;
	 		$content_file .= ' 			$data[\'message\'] = ($result) ? \'Eliminado!\' : \'mmm algo salio mal, no se pudo eliminar...\';' . PHP_EOL;
			$content_file .= '			$this->load->view(\'' . strtolower($table_name) . '\', $data);'			. PHP_EOL;
	 		$content_file .= '		}' 																. PHP_EOL;
			$content_file .=''																						. PHP_EOL;

	 		return $content_file;
		}

		/**
		 * [controller_string_end_class description]
		 * @return [type] [description]
		 */
		private function controller_string_end_class()
		{
			$content_file =' 	}'																. PHP_EOL;

			return $content_file;
		}

		/**
		 * [_get_attributes_from_table description]
		 * @return [type] [description]
		 */
		private function _get_attributes_from_table($columns_names_array)
		{
			/**
			 * $total_num;
			 * Returns the number of fields in the table iterated
			 * e.g: $total_num = 5;
			 */
			$total_num = count($columns_names_array);

			$index = 1;
			$content_file = '';
			foreach ($columns_names_array as $row_column)
			{
				if($row_column["extra"] != "auto_increment")
				{
					if($index != $total_num){
						$content_file .= " 				'" . $row_column['column_name'] . "' => \$item['" . $row_column['column_name'] . "']," . PHP_EOL;
					}
					else{
						$content_file .= " 				'" . $row_column['column_name'] . "' => \$item['" . $row_column['column_name'] . "']" . PHP_EOL;
					}
				}
				$index++;
			}

			return $content_file;
		}

		/**
		 * [_get_update_fields_inline description]
		 * @param  [type] $columns_names_array [description]
		 * @return [type]                      [description]
		 */
		private function _get_update_fields_inline($columns_names_array)
		{
			/**
			 * $total_num;
			 * Returns the number of fields in the table iterated
			 * e.g: $total_num = 5;
			 */
			$total_num = count($columns_names_array);
			$index = 1;
			$list = '';
			foreach ($columns_names_array as $row_column)
			{
				if($row_column["extra"] != "auto_increment")
				{
					if ($index != $total_num)
					{
						$list .= '$' .$row_column['column_name'] . ', ';
					} else
					{
						$list .= '$' .$row_column['column_name'] . '';
						$index = 1;
					}
				}
				$index++;
			}

			return $list;
		}

		/**
		 * [_get_update_fields_for_input description]
		 * @param  [type] $columns_names_array [description]
		 * @return [type]                      [description]
		 */
		private function _get_update_fields_for_input($columns_names_array)
		{
			$list = '';
			foreach ($columns_names_array as $row_column)
			{
				if($row_column["extra"] != "auto_increment")
				{
					$list .= '			$item[\'' . $row_column['column_name'] . '\'] = $' .$row_column['column_name'] . '_lol;' . PHP_EOL;
				}
			}

			return $list;
		}

		/**
		 * [_create_export_folder description]
		 * @return [type] [description]
		 */
		private function _create_export_folder()
		{
			/*Creating Folder Start*/
			$export_folder = $this->base_path . '/' . $this->base_export_folder;
			$this->create_folders($export_folder);
			/*Creating Folder End*/
		}

		/**
		 * [_create_models_folder description]
		 * @return [type] [description]
		 */
		private function _create_models_folder()
		{
			/*Creating Folder Start*/
			$export_model_folder = $this->base_path . '/'
								. $this->base_export_folder . '/'
								. $this->models_exported_folder;

			$this->create_folders($export_model_folder);
			/*Creating Folder End*/
		}

		/**
		 * [_create_controllers_folder description]
		 * @return [type] [description]
		 */
		private function _create_controllers_folder()
		{
			/*Creating Folder Start*/
			$export_controllers_folder = $this->base_path . '/'
									.  $this->base_export_folder . '/'
									. $this->controllers_exported_folder;

			$this->create_folders($export_controllers_folder);
			/*Creating Folder End*/
		}

		/**
		 * [create_folders description]
		 * @param  [type] $folder [description]
		 * @return [type]         [description]
		 */
		private function create_folders($folder)
		{
			if (!file_exists($folder)) {
				mkdir($folder, 0777, true);
			}
		}
	}

	$ci_generator = new CI_generator($this);
	$ci_generator->prefix_model = 'CI_';
	$ci_generator->prefix_controller = 'MY_';
	$ci_generator->models_exported_folder = '../../../models';
	$ci_generator->controllers_exported_folder = '../../../controllers';
	$ci_generator->generate();
