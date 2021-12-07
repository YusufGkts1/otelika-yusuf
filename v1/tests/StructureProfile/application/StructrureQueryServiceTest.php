<?php  

use \model\StructureProfile\application\StructureQueryService;
use \model\StructureProfile\application\Profile;
use \model\StructureProfile\application\DTO\GeoJsonSelection;
use \model\StructureProfile\application\DTO\PointSelection;
use \model\StructureProfile\infrastructure\GeoJsonStatementBuilder;

use \model\StructureProfile\application\IStatementBuilder;
use \model\StructureProfile\application\SQLStatement;
use \model\StructureProfile\application\IInhabitantProvider;
use \model\StructureProfile\application\IProfileProcessor;

use model\StructureProfile\application\exception\UnsupportedOperatorForSelectionException;

use model\StructureProfile\application\SpatialOperator;
use model\common\QueryObject;
use PHPUnit\Framework\TestCase;


class StructureQueryServiceTest extends TestCase {

  	  private $selection;

    	private static \DB $pos_db;
    	private static \DB $db;
    	private $structre_query_service;

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

            self::$db = new \DB(
              $config->get('db_structure_type'),
              $config->get('db_structure_hostname'),
              $config->get('db_structure_username'),
              $config->get('db_structure_password'),
              $config->get('db_structure_database'),
              $config->get('db_structure_port')
            );

            self::$pos_db->command("DELETE FROM feature");
            self::$pos_db->command("DELETE FROM structure");
            self::$pos_db->command("DELETE FROM csbm");
            self::$pos_db->command("DELETE FROM district");
    }

  	protected function setUp() : void {

        // $geojson = array(     
        //   'type' => 'Circle',
        //   'features' => array(
        //       'type' => 'Feature',
        //       "geometry" => array(
        //           'type' => 'Circle',
        //           'radius' => 2,
        //           'coordinates' => array(
        //                28.868850, 41.014571

        //               )
        //           )
        //       )
        //   );

        $geojson_str = '{"type":"Polygon","coordinates":[[[28.868932,41.014337],[28.868439,41.014498],[28.868669,41.014166], [28.869669,41.013666], [28.868932,41.014337]]]}';

        $geojson = json_decode($geojson_str, true);

        $this->selection = new GeoJsonSelection(4326, $geojson, null);


        // var_dump($this->selection->geojson()['coordinates'][0]);
        
       //  $field = "geometry";
       //  $params = array(
       //    ':srid' => $this->selection->srid(),
       //    ':longitude' => $this->selection->geojson()['coordinates'][0][0],
       //    ':latitude' => $this->selection->geojson()['coordinates'],
       //  );

      	// $sql_statement = new SQLStatement(

       //    $this->selection->operator()->getKey(). '("' . $field . '", ' . 'ST_SetSRID(geometry(ST_Buffer(ST_MakePoint(:longitude, :latitude), :radius)::geography), :srid)' . ')',

       //    $params
       //  );

        $second_ref = new ReflectionClass(Profile::class);
        $profile = $second_ref->newInstanceWithoutConstructor(); 

        $inhabitants = array();

        $statement_builder = new GeoJsonStatementBuilder($this->selection, 'geometry', $profile); //selection,field,profile

      	$inhabitant_provider = $this->createMock(IInhabitantProvider::class);
      	$inhabitant_provider->method('fetchInhabitantsOfIndependentSection')->willReturn($inhabitants);

        $profile_processor = $this->createMock(IProfileProcessor::class);
        $profile_processor->method('getSelectionStatement')->willReturn('feature');

      	$this->structre_query_service = new StructureQueryService(
          self::$pos_db, self::$db, $statement_builder, $inhabitant_provider, $profile_processor
        );
  	}


    public function test_If_getFeature_Returns_New_Feature_Query_DTO(){

        self::$pos_db->command( "INSERT INTO feature (id, cad_object_id, type, geometry) VALUES (1, 1, 'kale', geometry(POINT(2,2)) ) ");

        $second_ref = new ReflectionClass(Profile::class);
        $profile = $second_ref->newInstanceWithoutConstructor(); 

      	$feature = $this->structre_query_service->getFeature(1, $profile);

        $this->assertEquals($feature['id'], 1);
        $this->assertEquals($feature['cad_object_id'], 1);
        $this->assertEquals($feature['type'], 'kale');

 	  }

    public function test_If_fetchFeatures_Returns_Feature_Query_DTO(){

          $file = fopen('/Users/selimatasever/Desktop/GitHub/erp-api/v1/tests/repository/test.csv', 'r');
          
          $b = false;
          while (($lines = fgetcsv($file)) !== FALSE) {

              if(!$b) {       
                $b = true;    // first element of loop is ignored. 
                continue;
              }

          unset($lines[4]);    // geojson column ignored.

          $line = array($lines);

            foreach ($line as $feature) {

                if($feature[1] == "") {$feature[1] = null;}

                if($feature != null){
                  self::$pos_db->insert('feature', array(
                    'id' => $feature[0],
                    'cad_object_id' => $feature[1],
                    'type' => $feature[2],
                    'geometry' => $feature[3]
                  ));
                }
             }    
          }
       
        fclose($file);

        $order_by = array();
        $filter = array();

        $query_object = new QueryObject(
            $order_by,
            25,
            0,
            $filter
        );

        $second_ref = new ReflectionClass(Profile::class);
        $profile = $second_ref->newInstanceWithoutConstructor(); 

        $feature_dtos = $this->structre_query_service->fetchFeatures($this->selection, $query_object, $profile);

        $this->assertEquals($feature_dtos->current_page(), 1);
        $this->assertEquals($feature_dtos->total_pages(), 23);
        $this->assertEquals($feature_dtos->total_count(), 559);

    }


    public function test_If_fetchFeatures_Throws_Exception_When_Operator_Is_Within_And_Selection_Is_Point(){

        $this->expectException(UnsupportedOperatorForSelectionException::class);

        $geojson = array(     
          'type' => 'point',
          'features' => array(
              'type' => 'Feature',
              "geometry" => array(
                  'type' => 'point',
                  'coordinates' => array(
                       28.868932, 41.014337

                      )
                  )
              )
          );

        $selection = new GeoJsonSelection( 4326, $geojson, SpatialOperator::ST_Within() );

        $order_by = array();
        $filter = array();

        $query_object = new QueryObject(
            $order_by,
            25,
            0,
            $filter
        );

        $second_ref = new ReflectionClass(Profile::class);
        $profile = $second_ref->newInstanceWithoutConstructor(); 

        $feature_dtos = $this->structre_query_service->fetchFeatures($selection, $query_object, $profile);

        var_dump($feature_dtos);

    }


    public function test_If_fetchFeatures_Returns_Polygon_Results_Within_Given_Coordinates(){

        $geojson_str = '{"type":"Polygon","coordinates":[[[28.868932,41.014337],[28.868439,41.014498],[28.868669,41.014166]]]}';

        $geojson = json_decode($geojson_str, true);

        $selection = new GeoJsonSelection( 4326, $geojson, SpatialOperator::ST_Intersects() );

        $order_by = array();
        $filter = array();

        $query_object = new QueryObject(
            $order_by,
            25,
            0,
            $filter
        );

        $second_ref = new ReflectionClass(Profile::class);
        $profile = $second_ref->newInstanceWithoutConstructor(); 

        $feature_dtos = $this->structre_query_service->fetchFeatures($selection, $query_object, $profile);

        // var_dump($feature_dtos); DEVAMMMM

    }


    // public function test_If_fetchFeatures_Returns_Given_Specific_Area(){

    //     $geojson = array(     
    //       'type' => 'point',
    //       'features' => array(
    //           'type' => 'Feature',
    //           "geometry" => array(
    //               'type' => 'point',
    //               'coordinates' => array(
    //                    28.868850, 41.014571

    //                   )
    //               )
    //           )
    //       );

    //     $selection = new GeoJsonSelection(4326, $geojson, null);

    //     $order_by = array();
    //     $filter = array();

    //     $query_object = new QueryObject(
    //         $order_by,
    //         25,
    //         0,
    //         $filter
    //     );

    //     $second_ref = new ReflectionClass(Profile::class);
    //     $profile = $second_ref->newInstanceWithoutConstructor(); 

    //     $feature_dtos = $this->structre_query_service->fetchFeatures($selection, $query_object, $profile);

    // }


    public function test_If_search_Method_Returns_Results_And_Count_When_String_Matches_With_Structre_District_Or_CSBM(){ 

        self::$pos_db->command( "INSERT INTO structure (id, feature_id, ada, pafta, parsel, blok_adi, yol_alti_kat_sayisi,yol_ustu_kat_sayisi, bina_durum, bina_yapi_tipi, site_adi, posta_kodu, csbm_kodu,kod, es_bina_kimlik_no, es_bina_kodu) VALUES(1,1642,1155,2,1,'atasever apt',1,1,1,1,'Narman', 23143,4324,3141,441341,14124) " );

        self::$pos_db->command( "INSERT INTO csbm (kod, mahalle_kodu, ad, tip,gelismislik_durum, sabit_tanitim_numarasi) VALUES(42042,40499, 'Atasever Çavuş', 4,2,49) " );
        self::$pos_db->command( "INSERT INTO district (mahalle_kodu, feature_id, ad, mahalle_tip) VALUES(40404,4013, 'Atasever Mah', 1) " );

        $order_by = array();
        $filter = array();

        $query_object = new QueryObject(
            $order_by,
            25,
            0,
            $filter
        );

        $query_results = $this->structre_query_service->search('atasever'  , $query_object); 
        // first parameter is being searched in multiple tables.

        $this->assertEquals($query_results['results'][0]['type'], 'structure');
        $this->assertEquals($query_results['results'][1]['type'], 'district');
        $this->assertEquals($query_results['results'][2]['type'], 'csbm');

        $this->assertEquals($query_results['results'][0]['dbo']['blok_adi'], 'atasever apt');      // structre
        $this->assertEquals($query_results['results'][1]['dbo']['ad'], 'Atasever Mah');           // csbm
        $this->assertEquals($query_results['results'][2]['dbo']['ad'], 'Atasever Çavuş');        // district
        $this->assertEquals(3, $query_results['total']);
    }
}  

?>