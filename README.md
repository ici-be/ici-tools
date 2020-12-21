# ici-tools
Spatial PHP tools


WfsLayer
-----

Geoserver [WFS layer](https://docs.geoserver.org/stable/en/user/services/wfs/basics.html) queries in a PHP object.

```php
use ici\ici_tools\WfsLayer;

// 19 municipalities of the Brussels-Capital Region - Return everything
$wfs = new WfsLayer('https://geoservices-urbis.irisnet.be/geoserver/wfs', 'UrbisAdm:Mu');
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

// Limit query to municipalities beginning with "Woluwe" in French
$wfs->setCqlFilter("MU_NAME_FRE LIKE 'Woluwe%'");
// Limit retrieved attributes to MU_NAME_FRE, MU_NAME_DUT and GEOM (municipalities names in French and Dutch, and the geometry)
$wfs->setPropertyName('MU_NAME_FRE,MU_NAME_DUT,GEOM');

// Get the GET query
$wfs->getQueryUrl(); // https://geoservices-urbis.irisnet.be/geoserver/wfs?service=WFS&version=2.0.0&request=GetFeature&typeName=UrbisAdm%3AMu&outputFormat=json&resultType=results&propertyname=MU_NAME_FRE%2CMU_NAME_DUT%2CGEOM&cql_filter=MU_NAME_FRE+LIKE+%27Woluwe%25%27

// Return only the number of results
$wfs->getHits()

...
```
