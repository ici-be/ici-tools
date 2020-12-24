ici-tools
========
**ici-tools** is a PHP geo-related library, providing spatial tools. Contributions welcome :)

## Features
* **WfsLayer** helps create requests on a WFS layer using a PHP object. Web Feature Service (WFS) is a standard of the Open Geospatial Consortium.


## Installation
```
composer require ici-be/ici-tools
```
-----

## WfsLayer ##

Query a [WFS layer](https://docs.geoserver.org/stable/en/user/services/wfs/basics.html) with PHP (Geoserver / ArcGIS).

#### A. Typical usage with method chaining

* Example with a [BRIC.brussels](https://bric.brussels/) layer containing the 19 municipalities of the Brussels-Capital Region in Belgium

```php 
<?php

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

#### B. Available methods

* All setters can be chained and have public getters  

```php 
setVersion(string $version); // Change WFS version (default: "2.0.0")
setPropertyName(string $property_name); // To restrict requested attributes. You can specify a single attribute, or multiple attributes separated by commas.
setCqlFilter(string $cql_filter); // See the CQL_FILTER documentation: https://docs.geoserver.org/stable/en/user/tutorials/cql/cql_tutorial.html
setOutputSrs(int $output_srs); // Spatial reprojection using another SRS (ex: 4326 for EPSG:4326)
setMethod(string $method = 'POST'); // use GET or POST method (POST is used by default but is not always available)
setOutputFormat(string $output_format = 'json'); // The class needs a json/geojson format to work. If the default doesn't work, check with a getCapabilities Query. Sometimes it's called "GEOJSON" for example.
setStartIndex(int $start_index); // To start display results after x elements (for pagination)
setCount(int $count); // To limit the number of features returned
setSortBy(string $sort_by, string $order = 'ASC'); // To sort the returned selection based on an attribute value
```

* Other getters

```php 
getHits(); // Return only the number of results
getResults(); // Return the json data as a PHP object
getPropertiesArray(); // Return the rows and their properties in a PHP array
getQueryUrl(); // Return the query as a URL with GET parameters

```

#### C. More examples

```php
<?php

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
$wfs->setCqlFilter("MU_NAME_FRE LIKE 'Woluwe%'");

// 3. Limit retrieved attributes to MU_NAME_FRE, MU_NAME_DUT and GEOM (municipalities names in French and Dutch, and the geometry)
$wfs->setPropertyName('MU_NAME_FRE,MU_NAME_DUT,GEOM');

// 4. Return the url of the generated query
$wfs->getQueryUrl(); // https://geoservices-urbis.irisnet.be/geoserver/wfs?service=WFS&version=2.0.0&request=GetFeature&typeName=UrbisAdm%3AMu&outputFormat=json&resultType=results&propertyname=MU_NAME_FRE%2CMU_NAME_DUT%2CGEOM&cql_filter=MU_NAME_FRE+LIKE+%27Woluwe%25%27

// 5. Return the rows and their properties in an array
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
```


