<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users_model extends CI_Model
{
	private $table_name = 'users';

	public function select()
	{	
		if( !( $query = $this->db->get( $this->table_name ) ) ){
			return false;
		}
		
		return $query->result();
	}

	public function find( $data = array(), $fields = array() )
	{
		if ( !is_array( $data ) || ( is_array( $data ) && empty( $data ) ) ){
			return false;
		}

		if ( !is_array( $fields ) ){
			return false;
		}

		if ( count($fields) > 0 ) {

			$each_field = 1;
			$fields_str = '';

			foreach ($fields as $field) {

				$fields_str .= $field;

				if ($each_field < count($fields)) {
					$fields_str .= ', ';
				}

				$each_field++;
			}

			$this->db->select($fields_str);
		}

		if( !( $query = $this->db->get_where( $this->table_name, $data ) ) ){
			return false;
		}

		return $query->result();
	}

	public function insert( $data = array() )
	{
		if ( !is_array( $data ) || ( is_array( $data ) && empty( $data ) ) ){
			return false;
		}

		if( !$this->db->insert( $this->table_name, $data ) ){
			return false;
		}

		return $this->db->insert_id();
	}

	public function update( $id = false, $data = array() )
	{
		if ( !is_array( $data ) || ( is_array( $data ) && empty( $data ) ) ){
			return false;
		}

		if( !( is_numeric( $id ) && $id > 0 ) ) {
			return false;
		}

		$this->db->where( 'id', $id );
		if( !$this->db->update( $this->table_name, $data ) ){
			return false;
		}

		return true;
	}

	public function delete( $data = array() )
	{	
		if ( !is_array( $data ) || ( is_array( $data ) && empty( $data ) ) ){
			return false;
		}

		$this->db->where( $data );
		if( !$this->db->delete( $this->table_name ) ){
			return false;
		}

		return true;
	}

}
