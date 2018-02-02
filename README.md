Symfony Standard Edition
========================

## Prerequisites :
--------------
- php >= 7.0
- mongodb ^3.x

  * **mongodb extension** - http://php.net/manual/fr/mongodb.installation.manual.php

* composer install

* composer mongodb:build

# Update indexes for your documents
* php bin/console doctrine:mongodb:schema:update


## APi Methods :

All Methods shoud be send with Headers :
**Content-Type:application/json**
**X-Auth-Token:___TOKEN___**

 ** *Except signup and signin **

# Create account
 * POST /api/signup   
**Request :**
 {
	"email":"fzszes@mail.fr",
	"password":{
		"password":"ZADAZ1415SDFSDF5",
		"confpassword":"ZADAZ1415SDFSDF5"
	}

}

**Response :**
{
    "success": true,
    "data": {
        "id": "5a7490d9d2ccda0e7b318ea0",
        "value": "F6nI0+aKnNvDIYv5OVzPon/8qgXHwl3DxKoZBDrKO9ksRZ2ClfWm+ycVdCsUU4UwTVI=",
        "created_at": "2018-02-02T17:24:57+01:00",
        "user_id": "5a7490d9d2ccda0e7b318e9f"
    }
}

# SignIN
 * POST /api/signin  

 **Request :**
{
	"email":"fz@mail.fr",
	"password":"ZADAZ1415SDFSDF5"
}

**Response :**
{
    "success": true,
    "data": {
        "id": "5a749116d2ccda0e7b318ea1",
        "value": "Su0vPpIeUUnMpx7FCRH1433iNdwtwaMJ4IV4uTRuTHvO2tba9Dc/+65hGz/qX/08mDA=",
        "created_at": "2018-02-02T17:25:58+01:00",
        "user_id": "5a7318fed2ccda14b8423667"
    }
}

# Logout

* Delete /api/signout
**Response :**
{
    "success": true,
    "message": "successfuly signout"
}

# Get Shop list ordred by distance from connected user

* GET /api/shops/nearby?latitude=33.5781515&longitude=-7.627700099999999

**Response :**
{
   "success": true,
   "data": [
        {
            "id": "5a0c6711fb3aac66aafe26d0",
            "email": "leilaware@multiflex.com",
            "name": "Multiflex",
            "picture": "http://placehold.it/150x150",
            "city": "Rabat",
            "location": {
                "type": "Point",
                "coordinates": [
                    -6.84318,
                    33.91423
                ]
            },
            "distance": 81.592467538457
        },
        ........
   ]
}

# Get preferred shop
GET /api/shops/preferred

# Get preferred shop
GET /api/shop/disliked

# Like a shop
POST /api/shop/like

# unLike a shop
Delete /api/shop/unlike/{id}

# disLike a shop
POST /api/shop/dislike
