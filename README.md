# ici-tools
Spatial PHP tools


WfsLayer
-----

[WFS layer](https://docs.geoserver.org/stable/en/user/services/wfs/basics.html) queries in a PHP object (Geoserver / ArcGIS).


* Examples with a [BRIC.brussels](https://bric.brussels/) layer containing the 19 municipalities of the Brussels-Capital Region in Belgium

A. Typical usage with method chaining

```php 
use ici\ici_tools\WfsLayer;

// Construct layer with base path and layer name
$wfs = new WfsLayer('https://geoservices-urbis.irisnet.be/geoserver/wfs', 'UrbisAdm:Mu');

// Limit to municipalities greather than 10 km², order by name DESC, keep only the name in French
$wfs->setCqlFilter('AREA(GEOM)>10000000')->setSortBy('MU_NAME_FRE', 'DESC')->setPropertyName('MU_NAME_FRE');

// Dump properties as an array
var_dump($wfs->getPropertiesArray());
/*
array:4 [▼
  0 => [ "MU_NAME_FRE" => "Watermael-Boitsfort" ]
  1 => [ "MU_NAME_FRE" => "Uccle" ]
  2 => [ "MU_NAME_FRE" => "Bruxelles" ]
  3 => [ "MU_NAME_FRE" => "Anderlecht" ]
]
*/
```

B. Available methods

```php
use ici\ici_tools\WfsLayer;

// Construct layer with base path and layer name
$wfs = new WfsLayer('https://geoservices-urbis.irisnet.be/geoserver/wfs', 'UrbisAdm:Mu');

// 1. Return everything as an object
var_dump($wfs->getResults());
/*   
  +"type": "FeatureCollection"
  +"features": array:19 [▶]
  +"totalFeatures": 19
  +"numberMatched": 19
  +"numberReturned": 19
  +"timeStamp": "2020-12-21T19:27:58.952Z"
  +"crs": {#600 ▶}
  +"bbox": array:4 [▶] 
*/

// 2. Limit query to municipalities beginning with "Woluwe" in French
// See the CQL_FILTER documentation: https://docs.geoserver.org/stable/en/user/tutorials/cql/cql_tutorial.html 
$wfs->setCqlFilter("MU_NAME_FRE LIKE 'Woluwe%'");

// 3. Limit retrieved attributes to MU_NAME_FRE, MU_NAME_DUT and GEOM (municipalities names in French and Dutch, and the geometry)
$wfs->setPropertyName('MU_NAME_FRE,MU_NAME_DUT,GEOM');

// 4. Return the url of the generated query
$wfs->getQueryUrl(); // https://geoservices-urbis.irisnet.be/geoserver/wfs?service=WFS&version=2.0.0&request=GetFeature&typeName=UrbisAdm%3AMu&outputFormat=json&resultType=results&propertyname=MU_NAME_FRE%2CMU_NAME_DUT%2CGEOM&cql_filter=MU_NAME_FRE+LIKE+%27Woluwe%25%27

// 5. Return only the number of results
$wfs->getHits()

// 6. Return the rows and their properties in an array
$wfs->getPropertiesArray()
/*
array:2 [▼
  0 => array:3 [▼
    "MU_NAME_DUT" => "Sint-Pieters-Woluwe"
    "MU_NAME_FRE" => "Woluwe-Saint-Pierre"
    "MU_ID" => 8900
  ]
  1 => array:3 [▼
    "MU_NAME_DUT" => "Sint-Lambrechts-Woluwe"
    "MU_NAME_FRE" => "Woluwe-Saint-Lambert"
    "MU_ID" => 8800
  ]
]
*/

// 7. Send with GET method instead of POST
$wfs->setMethod('GET');

// 8. Change the name for the json/geojson outputFormat (default: "json")
$wfs->setOutputFormat('GEOJSON');

// 9. Define startIndex (useful along with setCount to page results of a GetFeature request. From WFS version 2.0.0 only)
$wfs->setStartIndex(10);

// 10. Change WFS version (default: "2.0.0")
$wfs->setVersion("1.1.0");
```


