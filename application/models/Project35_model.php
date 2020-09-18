<?php
class Project35_model extends CI_Model 
{

	public $projectId;
	public $bufferStock;

    function __construct()
    {
        parent::__construct();
        $this->projectId = 35;
        $this->bufferStock = 5;
    }
	
	public function getArticleData($articleData, $finalArticleData)
	{
		$finalData = $finalArticleData;
		
		//Collection
		if(isset($articleData['Portals_Collecties']) && $articleData['Portals_Collecties'] != '' && $articleData['Portals_Collecties'] != null)
		{
			$finalData['custom_attributes']['collection12'] = array(
				'type' => 'dropdown',
				'value' => $articleData['Portals_Collecties']
			);
		}
		
		//Glass(new)
		if(isset($articleData['Portals_Horloges_Glassoort']) && $articleData['Portals_Horloges_Glassoort'] != '' && $articleData['Portals_Horloges_Glassoort'] != null)
		{
			$finalData['custom_attributes']['glass12'] = array(
				'type' => 'dropdown',
				'value' => $articleData['Portals_Horloges_Glassoort']
			);
		}
		
		//CaseMaterial
		if(isset($articleData['Portals_Horloges_KastMaterialen']) && $articleData['Portals_Horloges_KastMaterialen'] != '' && $articleData['Portals_Horloges_KastMaterialen'] != null)
		{
			$finalData['custom_attributes']['casematerial12'] = array(
				'type' => 'dropdown',
				'value' => $articleData['Portals_Horloges_KastMaterialen']
			);
		}
		
		//StrapMaterial
		if(isset($articleData['Portals_Horloges_Bandmaterialen']) && $articleData['Portals_Horloges_Bandmaterialen'] != '' && $articleData['Portals_Horloges_Bandmaterialen'] != null)
		{
			$finalData['custom_attributes']['strapmaterial12'] = array(
				'type' => 'dropdown',
				'value' => $articleData['Portals_Horloges_Bandmaterialen']
			);
		}
		
		//Clasp Color
		if(isset($articleData['Portals_Horloges_SluitingKleuren']) && $articleData['Portals_Horloges_SluitingKleuren'] != '' && $articleData['Portals_Horloges_SluitingKleuren'] != null)
		{
			$finalData['custom_attributes']['clasp_color'] = array(
				'type' => 'dropdown',
				'value' => $articleData['Portals_Horloges_SluitingKleuren']
			);
		}
		
		//strapcolor
		if(isset($articleData['PortalsBandKleuren']) && $articleData['PortalsBandKleuren'] != '' && $articleData['PortalsBandKleuren'] != null)
		{
			$finalData['custom_attributes']['strapcolor'] = array(
				'type' => 'dropdown',
				'value' => $articleData['PortalsBandKleuren']
			);
		}
		
		//case_thickness
		if(isset($articleData['CaseThicknessMM__']) && $articleData['CaseThicknessMM__'] != '' && $articleData['CaseThicknessMM__'] != null)
		{
			$finalData['custom_attributes']['case_thickness'] = array(
				'type' => 'dropdown',
				'value' => $articleData['CaseThicknessMM__']
			);
		}
		
		//Movement
		if(isset($articleData['Portals_Horloges_Uurwerken']) && $articleData['Portals_Horloges_Uurwerken'] != '' && $articleData['Portals_Horloges_Uurwerken'] != null)
		{
			$finalData['custom_attributes']['movement'] = array(
				'type' => 'dropdown',
				'value' => $articleData['Portals_Horloges_Uurwerken']
			);
		}
		
		//Case colour
		if(isset($articleData['PortalsKastKleuren']) && $articleData['PortalsKastKleuren'] != '' && $articleData['PortalsKastKleuren'] != null)
		{
			$finalData['custom_attributes']['case_colour'] = array(
				'type' => 'dropdown',
				'value' => $articleData['PortalsKastKleuren']
			);
		}
		//Water
        if(isset($articleData['Waterdichtheid']) && $articleData['Waterdichtheid'] != '' && $articleData['Waterdichtheid'] != null)
		{
			$finalData['custom_attributes']['water_resistance_text'] = array(
				'type' => 'dropdown',
				'value' => $articleData['Waterdichtheid']
			);
		}
		
		//Dial colour
		if(isset($articleData['PortalsWijzerKleuren']) && $articleData['PortalsWijzerKleuren'] != '' && $articleData['PortalsWijzerKleuren'] != null)
		{
			$finalData['custom_attributes']['dial_colour'] = array(
				'type' => 'dropdown',
				'value' => $articleData['PortalsWijzerKleuren']
			);
		}
		
		//Case diameter
		if(isset($articleData['CaseWidthMM']) && $articleData['CaseWidthMM'] != '' && $articleData['CaseWidthMM'] != null)
		{
			$finalData['custom_attributes']['casediameter123'] = array(
				'type' => 'dropdown',
				'value' => $articleData['CaseWidthMM']
			);
		}
		
		//Gender
		if(isset($articleData['Portals_Gender']) && $articleData['Portals_Gender'] != '' && $articleData['Portals_Gender'] != null)
		{
			$finalData['custom_attributes']['gender1'] = array(
				'type' => 'dropdown',
				'value' => $articleData['Portals_Gender']
			);
		}
				
		//Serie
		if(isset($articleData['PortalsSeries']) && $articleData['PortalsSeries'] != '' && $articleData['PortalsSeries'] != null)
		{
			$finalData['custom_attributes']['series'] = array(
				'type' => 'dropdown',
				'value' => $articleData['PortalsSeries']
			);
		}
		
		//Designer
		if(isset($articleData['Portals_Designers']) && $articleData['Portals_Designers'] != '' && $articleData['Portals_Designers'] != null)
		{
			$finalData['custom_attributes']['designer'] = array(
				'type' => 'dropdown',
				'value' => $articleData['Portals_Designers']
			);
		}
		
		//EAN
		if(isset($articleData['EAN_code']) && $articleData['EAN_code'] != '' && $articleData['EAN_code'] != null)
		{
			$finalData['custom_attributes']['barcode'] = array(
				'type' => 'text',
				'value' => $articleData['EAN_code']
			);
		}
		
		//case_shape
		if(isset($articleData['Portals_Horloges_KastVormen']) && $articleData['Portals_Horloges_KastVormen'] != '' && $articleData['Portals_Horloges_KastVormen'] != null)
		{
			$finalData['custom_attributes']['case_shape'] = array(
				'type' => 'dropdown',
				'value' => $articleData['Portals_Horloges_KastVormen']
			);
		}
		
		//Strap length
		if(isset($articleData['StrapLengthMM']) && $articleData['StrapLengthMM'] != '' && $articleData['StrapLengthMM'] != null)
		{
			$finalData['custom_attributes']['strap_length'] = array(
				'type' => 'dropdown',
				'value' => $articleData['StrapLengthMM']
			);
		}
		
		//Movement Type
		if(isset($articleData['Portals_Uurwerk']) && $articleData['Portals_Uurwerk'] != '' && $articleData['Portals_Uurwerk'] != null)
		{
			$finalData['custom_attributes']['movement_type'] = array(
				'type' => 'dropdown',
				'value' => $articleData['Portals_Uurwerk']
			);
		}
		
		//Strap width
		if(isset($articleData['StrapWidthMM']) && $articleData['StrapWidthMM'] != '' && $articleData['StrapWidthMM'] != null)
		{
			$finalData['custom_attributes']['strap_width'] = array(
				'type' => 'dropdown',
				'value' => $articleData['StrapWidthMM']
			);
		}
		
		//Clasp type		
		if(isset($articleData['Portals_Horloges_Bandsluitsoorten']) && $articleData['Portals_Horloges_Bandsluitsoorten'] != '' && $articleData['Portals_Horloges_Bandsluitsoorten'] != null)
		{
			$finalData['custom_attributes']['clasp_type'] = array(
				'type' => 'dropdown',
				'value' => $articleData['Portals_Horloges_Bandsluitsoorten']
			);
		}		
		
		//Battery
		if(isset($articleData['Portals_Battery']) && $articleData['Portals_Battery'] != '' && $articleData['Portals_Battery'] != null)
		{
			$finalData['custom_attributes']['battery'] = array(
				'type' => 'dropdown',
				'value' => $articleData['Portals_Battery']
			);
		}		
		
		//Power reserve
		if(isset($articleData['Power_Reserve_indicator']) && $articleData['Power_Reserve_indicator'] != '')
		{
			if($articleData['Power_Reserve_indicator'] == "false")
			{
				$finalData['custom_attributes']['power_reserve'] = array(
				'type' => 'text',
				'value' => 0
				);
			}
			else
			{
				$finalData['custom_attributes']['power_reserve'] = array(
				'type' => 'text',
				'value' => 1
				);
			}
		}
		//Power reserve hours
		if(isset($articleData['Power_Reserve_hours']) && $articleData['Power_Reserve_hours'] != '' && $articleData['Power_Reserve_hours'] != null)
		{
			$finalData['custom_attributes']['power_reserve_hours'] = array(
				'type' => 'dropdown',
				'value' => $articleData['Power_Reserve_hours']
			);
		}
		
		//SecondHand
		if(isset($articleData['Second_Hand']) && $articleData['Second_Hand'] != '')
		{
			if($articleData['Second_Hand'] == "false")
			{
				$finalData['custom_attributes']['secondhand'] = array(
				'type' => 'text',
				'value' => 0
				);
			}
			else
			{
				$finalData['custom_attributes']['secondhand'] = array(
				'type' => 'text',
				'value' => 1
				);
			}
		}
				
		//Chronograph
		if(isset($articleData['Chronograph']) && $articleData['Chronograph'] != '')
		{
			if($articleData['Chronograph'] == "false")
			{
				$finalData['custom_attributes']['chronograph'] = array(
				'type' => 'text',
				'value' => 0
				);
			}
			else
			{
				$finalData['custom_attributes']['chronograph'] = array(
				'type' => 'text',
				'value' => 1
				);
			}
		}
		
		//Date
		if(isset($articleData['Date']) && $articleData['Date'] != '')
		{
			if($articleData['Date'] == "false")
			{
				$finalData['custom_attributes']['date'] = array(
				'type' => 'text',
				'value' => 0
				);
			}
			else
			{
				$finalData['custom_attributes']['date'] = array(
				'type' => 'text',
				'value' => 1
				);
			}
		}
		
		//Big Date
		if(isset($articleData['Big_Date']) && $articleData['Big_Date'] != '')
		{
			if($articleData['Big_Date'] == "false")
			{
				$finalData['custom_attributes']['bigdate'] = array(
				'type' => 'text',
				'value' => 0
				);
			}
			else
			{
				$finalData['custom_attributes']['bigdate'] = array(
				'type' => 'text',
				'value' => 1
				);
			}
		}
		
		//Week_Days
		if(isset($articleData['Week_Days']) && $articleData['Week_Days'] != '')
		{
			if($articleData['Week_Days'] == "false")
			{
				$finalData['custom_attributes']['weekday'] = array(
				'type' => 'text',
				'value' => 0
				);
			}
			else
			{
				$finalData['custom_attributes']['weekday'] = array(
				'type' => 'text',
				'value' => 1
				);
			}
		}
		
		//Months
		if(isset($articleData['Months']) && $articleData['Months'] != '')
		{
			if($articleData['Months'] == "false")
			{
				$finalData['custom_attributes']['months'] = array(
				'type' => 'text',
				'value' => 0
				);
			}
			else
			{
				$finalData['custom_attributes']['months'] = array(
				'type' => 'text',
				'value' => 1
				);
			}
		}

		//24hr_Hand
		if(isset($articleData['_4hr_Hand']) && $articleData['_4hr_Hand'] != '')
		{
			if($articleData['_4hr_Hand'] == "false")
			{
				$finalData['custom_attributes']['hour24'] = array(
				'type' => 'text',
				'value' => 0
				);
			}
			else
			{
				$finalData['custom_attributes']['hour24'] = array(
				'type' => 'text',
				'value' => 1
				);
			}
		}

		//seethrough
		if(isset($articleData['See_Through_Back']) && $articleData['See_Through_Back'] != '')
		{
			if($articleData['See_Through_Back'] == "false")
			{
				$finalData['custom_attributes']['seethrough'] = array(
				'type' => 'text',
				'value' => 0
				);
			}
			else
			{
				$finalData['custom_attributes']['seethrough'] = array(
				'type' => 'text',
				'value' => 1
				);
			}
		}
		
		//Week_nr
		if(isset($articleData['Week_nr']) && $articleData['Week_nr'] != '')
		{
			if($articleData['Week_nr'] == "false")
			{
				$finalData['custom_attributes']['weeknumber'] = array(
				'type' => 'text',
				'value' => 0
				);
			}
			else
			{
				$finalData['custom_attributes']['weeknumber'] = array(
				'type' => 'text',
				'value' => 1
				);
			}
		}
		
		//Radio_Controlled
		if(isset($articleData['Radio_Controlled']) && $articleData['Radio_Controlled'] != '')
		{
			if($articleData['Radio_Controlled'] == "false")
			{
				$finalData['custom_attributes']['radiocontrol'] = array(
				'type' => 'text',
				'value' => 0
				);
			}
			else
			{
				$finalData['custom_attributes']['radiocontrol'] = array(
				'type' => 'text',
				'value' => 1
				);
			}
		}
		
		//Screwed_Back
		if(isset($articleData['Screwed_Back']) && $articleData['Screwed_Back'] != '')
		{
			if($articleData['Screwed_Back'] == "false")
			{
				$finalData['custom_attributes']['screwedback'] = array(
				'type' => 'text',
				'value' => 0
				);
			}
			else
			{
				$finalData['custom_attributes']['screwedback'] = array(
				'type' => 'text',
				'value' => 1
				);
			}
		}
		
		//Screwed_Crown
		if(isset($articleData['Screwed_Crown']) && $articleData['Screwed_Crown'] != '')
		{
			if($articleData['Screwed_Crown'] == "false")
			{
				$finalData['custom_attributes']['screwedcrown'] = array(
				'type' => 'text',
				'value' => 0
				);
			}
			else
			{
				$finalData['custom_attributes']['screwedcrown'] = array(
				'type' => 'text',
				'value' => 1
				);
			}
		}
		
		//Open_Heart
		if(isset($articleData['Open_Heart']) && $articleData['Open_Heart'] != '')
		{
			if($articleData['Open_Heart'] == "false")
			{
				$finalData['custom_attributes']['openheart'] = array(
				'type' => 'text',
				'value' => 0
				);
			}
			else
			{
				$finalData['custom_attributes']['openheart'] = array(
				'type' => 'text',
				'value' => 1
				);
			}
		}
		
		//Skeleton
		if(isset($articleData['Skeleton']) && $articleData['Skeleton'] != '')
		{
			if($articleData['Skeleton'] == "false")
			{
				$finalData['custom_attributes']['skeleton'] = array(
				'type' => 'text',
				'value' => 0
				);
			}
			else
			{
				$finalData['custom_attributes']['skeleton'] = array(
				'type' => 'text',
				'value' => 1
				);
			}
		}
		//Portals_Materiaal
		if(isset($articleData['Portals_Materiaal']) && $articleData['Portals_Materiaal'] != '' && $articleData['Portals_Materiaal'] != null)
		{
			$finalData['custom_attributes']['jewel_material'] = array(
				'type' => 'dropdown',
				'value' => $articleData['Portals_Materiaal']
			);
		}

		//JewelleryWidthMM		
		if(isset($articleData['JewelleryWidthMM']) && $articleData['JewelleryWidthMM'] != '' && $articleData['JewelleryWidthMM'] != null)
		{
			$finalData['custom_attributes']['jewel_width'] = array(
				'type' => 'dropdown',
				'value' => $articleData['JewelleryWidthMM']
			);
		}
		
		//JewelleryThicknessMM
		if(isset($articleData['JewelleryThicknessMM']) && $articleData['JewelleryThicknessMM'] != '' && $articleData['JewelleryThicknessMM'] != null)
		{
			$finalData['custom_attributes']['jewel_thickness'] = array(
				'type' => 'dropdown',
				'value' => $articleData['JewelleryThicknessMM']
			);
		}	
		
		//JewelleryLengthCM
		if(isset($articleData['JewelleryLengthCM']) && $articleData['JewelleryLengthCM'] != '' && $articleData['JewelleryLengthCM'] != null)
		{
			$finalData['custom_attributes']['jewel_thickness'] = array(
				'type' => 'dropdown',
				'value' => $articleData['JewelleryLengthCM']
			);
		}

		//Portals_Ringmaten
		if(isset($articleData['Portals_Ringmaten']) && $articleData['Portals_Ringmaten'] != '' && $articleData['Portals_Ringmaten'] != null)
		{
			$finalData['custom_attributes']['ring_size'] = array(
				'type' => 'dropdown',
				'value' => $articleData['Portals_Ringmaten']
			);
		}

		//Portals_Stenen
		if(isset($articleData['Portals_Stenen']) && $articleData['Portals_Stenen'] != '' && $articleData['Portals_Stenen'] != null)
		{
			$finalData['custom_attributes']['stone_type'] = array(
				'type' => 'dropdown',
				'value' => $articleData['Portals_Stenen']
			);
		}
		
		//__stones
		if(isset($articleData['__stones']) && $articleData['__stones'] != '' && $articleData['__stones'] != null)
		{
			$finalData['custom_attributes']['stones'] = array(
				'type' => 'text',
				'value' => $articleData['__stones']
			);
		}

		//Caratweight
		if(isset($articleData['Caratweight']) && $articleData['Caratweight'] != '' && $articleData['Caratweight'] != null)
		{
			$finalData['custom_attributes']['carats'] = array(
				'type' => 'text',
				'value' => $articleData['Caratweight']
			);
		}

		//Portals_Stenen_Vorm
		if(isset($articleData['Portals_Stenen_Vorm']) && $articleData['Portals_Stenen_Vorm'] != '' && $articleData['Portals_Stenen_Vorm'] != null)
		{
			$finalData['custom_attributes']['stone_shape'] = array(
				'type' => 'dropdown',
				'value' => $articleData['Portals_Stenen_Vorm']
			);
		}

		//Portals_Stenen_Kwaliteit
		if(isset($articleData['Portals_Stenen_Kwaliteit']) && $articleData['Portals_Stenen_Kwaliteit'] != '' && $articleData['Portals_Stenen_Kwaliteit'] != null)
		{
			$finalData['custom_attributes']['stone_quality'] = array(
				'type' => 'dropdown',
				'value' => $articleData['Portals_Stenen_Kwaliteit']
			);
		}	
		
		//Portals_Stenen_Kleur
		if(isset($articleData['Portals_Stenen_Kleur']) && $articleData['Portals_Stenen_Kleur'] != '' && $articleData['Portals_Stenen_Kleur'] != null)
		{
			$finalData['custom_attributes']['stone_colour'] = array(
				'type' => 'dropdown',
				'value' => $articleData['Portals_Stenen_Kleur']
			);
		}		
		
		//Regulator
		if(isset($articleData['Regulator']) && $articleData['Regulator'] != '')
		{
			if($articleData['Regulator'] == "false")
			{
				$finalData['custom_attributes']['regulator'] = array(
				'type' => 'text',
				'value' => 0
				);
			}
			else
			{
				$finalData['custom_attributes']['regulator'] = array(
				'type' => 'text',
				'value' => 1
				);
			}
		}
		
		//Portals_Tijdsaanduiding
		if(isset($articleData['Portals_Tijdsaanduiding']) && $articleData['Portals_Tijdsaanduiding'] != '' && $articleData['Portals_Tijdsaanduiding'] != null)
		{
			$finalData['custom_attributes']['display'] = array(
				'type' => 'dropdown',
				'value' => $articleData['Portals_Tijdsaanduiding']
			);
		}

		//Portals_Horloges_Wijzerplaatdetails
		if(isset($articleData['Portals_Horloges_Wijzerplaatdetails']) && $articleData['Portals_Horloges_Wijzerplaatdetails'] != '' && $articleData['Portals_Horloges_Wijzerplaatdetails'] != null)
		{
			$finalData['custom_attributes']['dial_details'] = array(
				'type' => 'dropdown',
				'value' => $articleData['Portals_Horloges_Wijzerplaatdetails']
			);
		}

		//UnitWeightGr
		if(isset($articleData['UnitWeightGr']) && $articleData['UnitWeightGr'] != '' && $articleData['UnitWeightGr'] != null)
		{
			$finalData['custom_attributes']['unitweight'] = array(
				'type' => 'text',
				'value' => $articleData['UnitWeightGr']
			);
		}

		//World_Timer
		if(isset($articleData['World_Timer']) && $articleData['World_Timer'] != '')
		{
			if($articleData['World_Timer'] == "false")
			{
				$finalData['custom_attributes']['worldtimer'] = array(
				'type' => 'text',
				'value' => 0
				);
			}
			else
			{
				$finalData['custom_attributes']['worldtimer'] = array(
				'type' => 'text',
				'value' => 1
				);
			}
		}
		
		//Depth_Meter
		if(isset($articleData['Depth_Meter']) && $articleData['Depth_Meter'] != '')
		{
			if($articleData['Depth_Meter'] == "false")
			{
				$finalData['custom_attributes']['depthmeter'] = array(
				'type' => 'text',
				'value' => 0
				);
			}
			else
			{
				$finalData['custom_attributes']['depthmeter'] = array(
				'type' => 'text',
				'value' => 1
				);
			}
		}
		
		//Stopwatch
		if(isset($articleData['Stopwatch']) && $articleData['Stopwatch'] != '')
		{
			if($articleData['Stopwatch'] == "false")
			{
				$finalData['custom_attributes']['stopwatch'] = array(
				'type' => 'text',
				'value' => 0
				);
			}
			else
			{
				$finalData['custom_attributes']['stopwatch'] = array(
				'type' => 'text',
				'value' => 1
				);
			}
		}
		
		//tachymeter
		if(isset($articleData['Tachymeter']) && $articleData['Tachymeter'] != '')
		{
			if($articleData['Tachymeter'] == "false")
			{
				$finalData['custom_attributes']['tachymeter'] = array(
				'type' => 'text',
				'value' => 0
				);
			}
			else
			{
				$finalData['custom_attributes']['tachymeter'] = array(
				'type' => 'text',
				'value' => 1
				);
			}
		}
		
		//Alarm
		if(isset($articleData['Alarm']) && $articleData['Alarm'] != '')
		{
			if($articleData['Alarm'] == "false")
			{
				$finalData['custom_attributes']['alarm'] = array(
				'type' => 'text',
				'value' => 0
				);
			}
			else
			{
				$finalData['custom_attributes']['alarm'] = array(
				'type' => 'text',
				'value' => 1
				);
			}
		}
		
		//GMT
		if(isset($articleData['GMT']) && $articleData['GMT'] != '')
		{
			if($articleData['GMT'] == "false")
			{
				$finalData['custom_attributes']['gmt'] = array(
				'type' => 'text',
				'value' => 0
				);
			}
			else
			{
				$finalData['custom_attributes']['gmt'] = array(
				'type' => 'text',
				'value' => 1
				);
			}
		}	
		
		//Retrograde
		if(isset($articleData['Retrograde']) && $articleData['Retrograde'] != '')
		{
			if($articleData['Retrograde'] == "false")
			{
				$finalData['custom_attributes']['retrograde'] = array(
				'type' => 'text',
				'value' => 0
				);
			}
			else
			{
				$finalData['custom_attributes']['retrograde'] = array(
				'type' => 'text',
				'value' => 1
				);
			}
		}
		
		//Moon_Phase
		if(isset($articleData['Moon_Phase']) && $articleData['Moon_Phase'] != '')
		{
			if($articleData['Moon_Phase'] == "false")
			{
				$finalData['custom_attributes']['moon_phase'] = array(
				'type' => 'text',
				'value' => 0
				);
			}
			else
			{
				$finalData['custom_attributes']['moon_phase'] = array(
				'type' => 'text',
				'value' => 1
				);
			}
		}
		
		//Diver
		if(isset($articleData['Diver']) && $articleData['Diver'] != '')
		{
			if($articleData['Diver'] == "false")
			{
				$finalData['custom_attributes']['diver'] = array(
				'type' => 'text',
				'value' => 0
				);
			}
			else
			{
				$finalData['custom_attributes']['diver'] = array(
				'type' => 'text',
				'value' => 1
				);
			}
		}
		
		//New arrival van
		if(isset($articleData['NewFrom']) && $articleData['NewFrom'] != '')
		{
			$date = explode('T', $articleData['NewFrom']);
			$date = $date[0];
			
			$finalData['custom_attributes']['news_from_date'] = array(
				'type' => 'text',
				'value' => $date
			);
		}
		
		//New arrival t/m
		if(isset($articleData['NewUntil']) && $articleData['NewUntil'] != '')
		{
			$date = explode('T', $articleData['NewUntil']);
			$date = $date[0];
			
			$finalData['custom_attributes']['news_to_date'] = array(
				'type' => 'text',
				'value' => $date
			);
		}
		
		//SEOMetaTitle
		if(isset($articleData['SEOMetaTitle']) && $articleData['SEOMetaTitle'] != '')
		{
		    $finalData['custom_attributes']['meta_title'] = array(
		        'type' => 'text',
		        'value' => $articleData['SEOMetaTitle']
		    );
		}		
		//SEOMetaKeywords
		if(isset($articleData['SEOMetaKeywords']) && $articleData['SEOMetaKeywords'] != '')
		{
		    $finalData['custom_attributes']['meta_keyword'] = array(
		        'type' => 'text',
		        'value' => $articleData['SEOMetaKeywords']
		    );
		}		
		//SEOMetaDescription
		if(isset($articleData['SEOMetaDescription']) && $articleData['SEOMetaDescription'] != '')
		{
		    $finalData['custom_attributes']['meta_description'] = array(
		        'type' => 'text',
		        'value' => $articleData['SEOMetaDescription']
		    );
		}		
		
		//=======================================
		//======== Over schrijven Basis velden ==
		//=======================================		
		
		//Lange omschrijving
		if(isset($articleData['OmschrijvingLang']) && $articleData['OmschrijvingLang'] != '' && $articleData['OmschrijvingLang'] != null)
		{
			$finalData['description'] = $articleData['OmschrijvingLang'];
		}
		
		//Product name overwrite
		$sNewProductName = "";
		
		if(isset($articleData['NameMagento']) && $articleData['NameMagento'] != '' && $articleData['NameMagento'] != null) // Als Eigen naam is ingevuld, deze schrijven
		{
			$sNewProductName .= $articleData['NameMagento'];
		}
		//Zo niet, Portals_Series (als deze leeg is dan moet het Portals_Series_Sort zijn
		//Portals_Horloges_KastKleuren & Portals_Horloges_WijzerplaatKleuren & Portals_Horloges_BandKleuren.
		else
		{
			if(isset($articleData['PortalsSeries']) && $articleData['PortalsSeries'] != '' && $articleData['PortalsSeries'] != null)
			{
				$sNewProductName .= $articleData['PortalsSeries'];
			}
			else if(isset($articleData['Portals_Themas']) && $articleData['Portals_Themas'] != '' && $articleData['Portals_Themas'] != null)
			{
				// //Same as above, but value does not exist
				$sNewProductName .= $articleData['Portals_Themas'];
			}
			$sNewProductName .= " ";
			if(isset($articleData['PortalsKastKleuren']) && $articleData['PortalsKastKleuren'] != '' && $articleData['PortalsKastKleuren'] != null){
			$sNewProductName .= $articleData['PortalsKastKleuren'];
			}
			$sNewProductName .= " ";
			if(isset($articleData['PortalsWijzerKleuren']) && $articleData['PortalsWijzerKleuren'] != '' && $articleData['PortalsWijzerKleuren'] != null){
				$sNewProductName .= $articleData['PortalsWijzerKleuren'];
			}
			$sNewProductName .= " ";
			if(isset($articleData['PortalsBandKleuren']) && $articleData['PortalsBandKleuren'] != '' && $articleData['PortalsBandKleuren'] != null){
			$sNewProductName .= $articleData['PortalsBandKleuren'];
			}
		}
		if (strlen($sNewProductName) > 4)
		{
			$finalData['name'] = $sNewProductName;
		}
		
		//Buffer Stock
		$qty = $this->getItemQty($articleData['ItemCode']) - $this->bufferStock;
		if($qty < 0){
			$qty = 0;
		}
		$finalData['quantity'] = $qty;

		// Custom price connector
		$price = $this->getItemPrice($articleData['ItemCode']);
		$finalData['price'] = $price;
		
		//////// Ja / Nee Veld
		//if(isset($articleData['Ingeschakeld']) && $articleData['Ingeschakeld'] != '')
		//{
			//if($articleData['Ingeschakeld'] == "false")
			//{
			//	$finalData['status'] = 1;
			//}
			//else
			//{
			//	$finalData['status'] = 2;
			//}
		//}
		
		
		//Koppeling tussen InShop
		if(isset($articleData['Ingeschakeld']) && $articleData['Ingeschakeld'] != '')
		{
		    //log_message('debug', 'Weisz param: '. var_export($articleData, true));
        
			if($articleData['Ingeschakeld'] == 'true')
			{
    			$finalData['tmp']['status'] = 1;
    			//log_message('debug', 'Weisz TRUE: '. $articleData['Ingeschakeld']);
			} else {
				$finalData['tmp']['status'] = 2;
				//log_message('debug', 'Weisz FALSE: '. $articleData['Ingeschakeld']);
			}
		}
		
		
		
		
		
		return $finalData;
	}
	
	public function getStockArticleData($article, $finalArticleData){
		//Buffer Stock
		$qty = $this->getItemQty($article['ItemCode']) - $this->bufferStock;
		if($qty < 0){
			$qty = 0;
		}
		$finalArticleData['quantity'] = $qty;
		return $finalArticleData;
	}
	
	public function getItemQty($itemCode){
		$projectId = $this->projectId;
		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
		
		$this->load->helper('NuSOAP/nusoap');
		
		$client = new nusoap_client($afasGetUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		
		if($itemCode != ''){
			$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="Itemcode" OperatorType="1">'.$itemCode.'</Field></Filter></Filters>';
		}
		
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorId'] = "Voorraad_Magazijn_App";
		$xml_array['filtersXml'] = $filtersXML;
		$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>0</Skip><Take>1</Take></options>';
		
		$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
		$resultData = $result["GetDataWithOptionsResult"];
		$resultData = preg_replace('/[^(\x20-\x7f)]*/s','',$resultData);

		$data = simplexml_load_string($resultData);
		if(isset($data->Voorraad_Magazijn_App) && count($data->Voorraad_Magazijn_App) > 0){
			$itemData = $this->Afas_model->xml2array($data->Voorraad_Magazijn_App);
			if(!empty($itemData)){
				return intval($itemData['Op_voorraad']) - intval($itemData['Gereserveerd_op_voorraad']);
			}
		}
		return 0;
	}
	
	public function getItemPrice($itemCode){
		$projectId = $this->projectId;
		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
		
		$this->load->helper('NuSOAP/nusoap');
		
		$client = new nusoap_client($afasGetUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		
		if($itemCode != ''){
			$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="Itemcode" OperatorType="1">'.$itemCode.'</Field></Filter></Filters>';
		}
		
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorId'] = "Verkoopprijs_App";
		$xml_array['filtersXml'] = $filtersXML;
		$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>0</Skip><Take>1</Take></options>';
		
		$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
		$resultData = $result["GetDataWithOptionsResult"];
		$resultData = preg_replace('/[^(\x20-\x7f)]*/s','',$resultData);

		$data = simplexml_load_string($resultData);
		if(isset($data->Verkoopprijs_App) && count($data->Verkoopprijs_App) > 0){
			$itemData = $this->Afas_model->xml2array($data->Verkoopprijs_App);
			if(!empty($itemData)){
				return floatval($itemData['Consumentenprijs']);
			}
		}
		return 0;
	}
	
	public function loadCategories($finalArticleData, $article, $projectId){
		$finalArticleData['categories_ids'] = 781;
		return $finalArticleData;
	}
	
	public function checkConfigurable($saveData, $productData, $projectId, $type = '')
	{
		if($type == 'update'){
			foreach($saveData['product']['custom_attributes'] as $index => $customAttribute){
				if($customAttribute['attribute_code'] == 'category_ids'){
					unset($saveData['product']['custom_attributes'][$index]);
				}
			}
		}
		
		//if(isset($productData['Ingeschakeld']) && $productData['Ingeschakeld'] != ''){
		//	if($productData['Ingeschakeld'] == true || $productData['Ingeschakeld'] == 'true'){
		//		$saveData['product']['status'] = 1;
		//	} else {
		//		$saveData['product']['status'] = 2;
		//	}
		//}
		
		if(isset($productData['tmp']) && isset($productData['tmp']['status']))
		{
   			$saveData['product']['status'] = $productData['tmp']['status'];
		}
		unset($productData['tmp']);
		
		
		return $saveData;
	}
	
	public function setOrderParams($fields, $orderData){
		$fields->DbId = 20000;
		$fields->OrPr = 6;
	}
	
    public function loadCustomOrderAttributes($appendItem, $order, $projectId){
	    if($appendItem['state'] != 'processing' && $appendItem['state'] != 'complete'){
			return false;
	    }
	    return $appendItem;
    }
	
	function customCronjob(){
		$this->load->model('Projects_model');
		$this->load->model('Afas_model');
		$this->load->model('Cms_model');
		$this->load->model('Magento2_model');
		
		$project = $this->db->get_where('projects', array('id' => 35))->row_array();
		// Check if enabled
		if($this->Projects_model->getValue('enabled', $project['id']) != '1'){
			return;
		}
		
		if($this->input->get('project') != '' && $this->input->get('project') != $project['id']){
			return;
		}
		
		log_message('debug', 'Custom job ping: '. var_export($project['id'], true));
		
		// Get credentials
		$storeUrl = $project['store_url'];
		$apiKey = $project['api_key'];
		$pluginKey = $project['plugin_key'];
		$storeKey = $project['store_key'];
		
		// Send orders combined per day
		$lastExecution = $this->Projects_model->getValue('orders_last_execution', $project['id']);
		$interval = $this->Projects_model->getValue('orders_interval', $project['id']);
		if(($lastExecution == '' || $lastExecution + ($interval * 60) <= time())){
			$currentOrderOffset = $this->Projects_model->getValue('orders_offset', $project['id']) ? $this->Projects_model->getValue('orders_offset', $project['id']) : 0;
			$orderAmount = $this->Projects_model->getValue('orders_amount', $project['id']);
			
			$orders = $this->Cms_model->getOrders($project['id'], $currentOrderOffset, $orderAmount);
			$orders = isset($orders['orders']) ? $orders['orders'] : array();
            
            //log_message('debug', 'Custom job ping: '. var_export($orders, true));
            
			if($orders != false && !empty($orders)){				
				$this->Projects_model->saveValue('orders_offset', $currentOrderOffset + count($orders), $project['id']);
				$this->Projects_model->saveValue('orders_last_execution', time(), $project['id']);
				
				log_message('debug', 'Custom job ping, test: '. var_export($orders, true));
				
				foreach($orders as $order){
					$order['customer'] = array();
					$order['customer']['email'] = 'sales@danishdesign.nl';
					$result = $this->Afas_model->sendOrder($project['id'], $order);
				}
			}
		}
	}
}