<?php

use model\StructureProfile\infrastructure\GeoJsonStatementBuilder;
use model\StructureProfile\application\Profile;
use model\StructureProfile\application\DTO\GeoJsonSelection;
use model\StructureProfile\application\DTO\SelectionType;

use model\StructureProfile\application\exception\InvalidSelectionTypeException;

use PHPUnit\Framework\TestCase;


class GeoJsonStatementBuilderTest extends TestCase {

	private static \DB $pos_db;
	public static function setUpBeforeClass() : void {
	
    	global $framework;
        $config = $framework->get('config');

        self::$pos_db = new \DB(
            $config->get('pos_db_gis_type'),
            $config->get('pos_db_gis_hostname'),
            $config->get('pos_db_gis_username'),
            $config->get('pos_db_gis_password'),
            $config->get('pos_db_gis_database'),
            $config->get('pos_db_gis_port')
        );	

   		self::$pos_db->command("DELETE FROM feature");

    }

	public function test_If_StatementBuilder_Returns_Sql_Statement_Correctly() {

       	self::$pos_db->command("CREATE TABLE IF NOT EXISTS feature (
		    id SERIAL PRIMARY KEY, 
		    cad_object_id INT NULL, 
		    type VARCHAR(32) NOT NULL, 
		    geometry GEOMETRY NOT NULL  
		)"); 


		self::$pos_db->command( "INSERT INTO feature (id, cad_object_id, type, geometry) VALUES (1, 1, 'kale', geometry(POINT(2,2)) ) ");

		$reflection = new ReflectionClass(GeoJsonStatementBuilder::class);
		$statement_builder = $reflection->newInstanceWithoutConstructor(); 
		

	   	$geojson = array(     // <- geojson in php format 
        'type' => 'Point',
        'features' => array(
            'type' => 'Feature',
            "geometry" => array(
                'type' => 'Point',
                'coordinates' => array(
                     [125.6, 10.1],
                     [125.6, 10.2]
                    )
                )
            )
        );

		$geo_json_selection = new GeoJsonSelection(1, $geojson, null);

		$selection = array($geo_json_selection, SelectionType::Point() );
		$field     = 'field'; 

		$second_ref = new ReflectionClass(Profile::class);
		$profile = $second_ref->newInstanceWithoutConstructor(); 

		$sql_statement = $statement_builder->buildStatement($geo_json_selection, $field, $profile);

		$this->assertEquals($geojson['type'], 'Point');
		$this->assertEquals($geojson['features']['geometry']['coordinates'][0][0], 125.6);
		$this->assertEquals($geojson['features']['geometry']['coordinates'][0][1], 10.1);
	}


	public function test_If_Exception_Is_Thrown_When_Invalid_Selection_Type_Is_Provided() {

		$this->expectException(InvalidSelectionTypeException::class);

		$reflection = new ReflectionClass(GeoJsonStatementBuilder::class);
		$statement_builder = $reflection->newInstanceWithoutConstructor(); 
		

	   	$geojson = array(     
        'type' => 'Point',
        'features' => array(
            'type' => 'Feature',
            "geometry" => array(
                'type' => 'Point',
                'coordinates' => array(
                     [125.6, 10.1],
                     [125.6, 10.2]
                    )
                )
            )
        );

        $arr = array(); // array without selection type must throw an exception.

		$geo_json_selection = new GeoJsonSelection(1, $arr , null);

		$selection = array($geo_json_selection, SelectionType::Point() );
		$field     = 'field'; 

		$second_ref = new ReflectionClass(Profile::class);
		$profile = $second_ref->newInstanceWithoutConstructor(); 

		$sql_statement = $statement_builder->buildStatement($geo_json_selection, $field, $profile);

		$this->assertEquals($geojson['type'], 'Point');
		$this->assertEquals($geojson['features']['geometry']['coordinates'][0][0], 125.6);
		$this->assertEquals($geojson['features']['geometry']['coordinates'][0][1], 10.1);
	}
}

?>