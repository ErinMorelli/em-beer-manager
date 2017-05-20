<?php
/**
 * Copyright (c) 2013-2017, Erin Morelli.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * @package EMBM\Admin\UTFB\Dummy
 */

function EMBM_Admin_Utfb_Dummy_response($request_url)
{
    $request = explode('/', $request_url);
    $request = end($request);

    $responses = array(
        'current_user' => '{
          "current_user": {
            "id": 1,
            "email": "erin@erinmorelli.com",
            "created_at": "2016-01-06T19:12:06Z",
            "updated_at": "2016-06-15T14:16:36Z",
            "role": "owner",
            "admin": false,
            "business_id": 1,
            "untappd_username": "ErinMorelli",
            "name": "Erin Morelli"
          }
        }',
        'locations' => '{
          "locations": [
            {
              "id": 3,
              "name": "Pied Piper",
              "formatted_address": "21 S Front St, Wilmington, NC 28401, USA",
              "full_address": "21 S Front St, , Wilmington, North Carolina, 28401, United States",
              "address1": "21 S Front St",
              "address2": "",
              "city": "Wilmington",
              "postcode": "28401",
              "region": "North Carolina",
              "country": "United States",
              "latitude": "34.2346553",
              "longitude": "-77.9482581",
              "website": "http://business.untappd.com",
              "contact_email": "Erlich.Bachman@example.com",
              "phone": "910-333-1245",
              "untappd_venue_id": 4174364,
              "created_at": "2016-06-16T19:29:59Z",
              "updated_at": "2016-06-16T19:50:32Z",
              "business_id": 3,
              "cross_streets": "",
              "neighborhood": "Downtown Wilmington",
              "timezone": "Eastern Time (US & Canada)",
              "tz_offset": -4,
              "directions": "Turn right at the fork.",
              "description": "Erlich Bachman\'s Incubator (A.K.A his House)",
              "in_business_since": 2021,
              "number_of_taps": null,
              "food_served": "food_served_yes",
              "growler_filling_station": "growler_filling_station_no",
              "crowler_filling_station": "crowler_filling_station_no",
              "kegs": "kegs_yes",
              "nitro_on_tap": "nitro_on_tap_no",
              "cask_on_tap": "cask_on_tap_no",
              "serve_wine": "serve_wine_yes",
              "serve_cocktails": "serve_cocktails_yes",
              "serve_liquor": "serve_liquor_yes",
              "outdoor_seating": "outdoor_seating_yes",
              "pet_friendly": "pet_friendly_yes",
              "contact_name": "Starbuck",
              "contact_phone": "(910) 333 - 1245",
              "published_at_untappd": null,
              "published_at_embed": null,
              "published_at_facebook": null,
              "published_count": 0,
              "currency": "USD",
              "currency_symbol": "$",
              "suspended": false,
              "accepted_payment_types": [
                "visa",
                "master_card",
                "american_express",
                "discover",
                "check"
              ],
              "twitter_linked": false,
              "facebook_post_page": false,
              "facebook_menu_page": false,
              "country_name": "United States",
              "website_url": "http://business.untappd.com"
            },
            {
              "id": 3,
              "name": "Pied Piper 2",
              "formatted_address": "21 S Front St, Wilmington, NC 28401, USA",
              "full_address": "21 S Front St, , Wilmington, North Carolina, 28401, United States",
              "address1": "21 S Front St",
              "address2": "",
              "city": "Wilmington",
              "postcode": "28401",
              "region": "North Carolina",
              "country": "United States",
              "latitude": "34.2346553",
              "longitude": "-77.9482581",
              "website": "http://business.untappd.com",
              "contact_email": "Erlich.Bachman@example.com",
              "phone": "910-333-1245",
              "untappd_venue_id": 4174364,
              "created_at": "2016-06-16T19:29:59Z",
              "updated_at": "2016-06-16T19:50:32Z",
              "business_id": 3,
              "cross_streets": "",
              "neighborhood": "Downtown Wilmington",
              "timezone": "Eastern Time (US & Canada)",
              "tz_offset": -4,
              "directions": "Turn right at the fork.",
              "description": "Erlich Bachman\'s Incubator (A.K.A his House)",
              "in_business_since": 2021,
              "number_of_taps": null,
              "food_served": "food_served_yes",
              "growler_filling_station": "growler_filling_station_no",
              "crowler_filling_station": "crowler_filling_station_no",
              "kegs": "kegs_yes",
              "nitro_on_tap": "nitro_on_tap_no",
              "cask_on_tap": "cask_on_tap_no",
              "serve_wine": "serve_wine_yes",
              "serve_cocktails": "serve_cocktails_yes",
              "serve_liquor": "serve_liquor_yes",
              "outdoor_seating": "outdoor_seating_yes",
              "pet_friendly": "pet_friendly_yes",
              "contact_name": "Starbuck",
              "contact_phone": "(910) 333 - 1245",
              "published_at_untappd": null,
              "published_at_embed": null,
              "published_at_facebook": null,
              "published_count": 0,
              "currency": "USD",
              "currency_symbol": "$",
              "suspended": false,
              "accepted_payment_types": [
                "visa",
                "master_card",
                "american_express",
                "discover",
                "check"
              ],
              "twitter_linked": false,
              "facebook_post_page": false,
              "facebook_menu_page": false,
              "country_name": "United States",
              "website_url": "http://business.untappd.com"
            }
          ]
        }',
        'menus' => '{
          "menus": [
            {
              "id": 1,
              "location_id": 3,
              "uuid": "98e37027-3f8e-45c7-b9a0-4984128be836",
              "unpublished": true,
              "name": "Draft Beer List",
              "description": "All the draft beers.",
              "footer": "Heavy vs. Light or Good vs. Evil? You Decide.",
              "created_at": "2016-06-16T19:53:37Z",
              "updated_at": "2016-06-16T19:57:33Z",
              "published_at_embed": null,
              "published_at_untappd": null,
              "published_at_facebook": null,
              "position": 0
            }
          ]
        }',
        'sections' => '{
          "sections": [
            {
              "id": 1,
              "menu_id": 1,
              "position": 0,
              "name": "Heavy",
              "description": "All our heavy draft beers.",
              "type":"Section",
              "created_at": "2016-06-16T19:53:37Z",
              "updated_at": "2016-06-16T19:55:48Z"
            },
            {
              "id": 2,
              "menu_id": 1,
              "position": 1,
              "name": "Light",
              "description": "All our light beers.",
              "type":"Section",
              "created_at": "2016-06-16T19:55:58Z",
              "updated_at": "2016-06-16T19:56:40Z"
            },
            {
              "id": 3,
              "menu_id": 1,
              "position": 0,
              "name": "On Deck",
              "description": "",
              "type": "OnDeckSection",
              "created_at": "2016-06-16T19:53:37Z",
              "updated_at": "2016-06-16T19:55:48Z"
            }
          ]
        }',
        'items' => '{
          "items": [
            {
              "id": 3,
              "section_id": 1,
              "position": 0,
              "untappd_id": 4473,
              "label_image": "https://untappd.akamaized.net/site/beer_logos/beer-_4473_8122008947804818c90640a06d83.jpeg",
              "brewery_location": "Dublin 8",
              "abv": "4.3",
              "ibu": "45.0",
              "cask": false,
              "nitro": false,
              "tap_number": null,
              "rating": "3.81587",
              "in_production": true,
              "untappd_beer_slug": "guinness-guinness-draught",
              "untappd_brewery_id": 49,
              "name": "Guinness Draught",
              "original_name": "Guinness Draught",
              "custom_name": null,
              "description": "Swirling clouds tumble as the storm begins to calm. Settle. Breathe in the moment, then break through the smooth, light head to the bittersweet reward.\n\nUnmistakeably GUINNESS, from the first velvet sip to the last, lingering drop. And every deep-dark satisfying mouthful in between.\n\nPure beauty. Pure GUINNESS.\n\nGuinness Draught is sold in kegs, widget cans, and bottles. The ABV varies from 4.1 to 4.3%.\n\nGuinness Extra Cold is the exact same beer only served through a super cooler at 3.5 °C",
              "custom_description": null,
              "original_description": "Swirling clouds tumble as the storm begins to calm. Settle. Breathe in the moment, then break through the smooth, light head to the bittersweet reward.\n\nUnmistakeably GUINNESS, from the first velvet sip to the last, lingering drop. And every deep-dark satisfying mouthful in between.\n\nPure beauty. Pure GUINNESS.\n\nGuinness Draught is sold in kegs, widget cans, and bottles. The ABV varies from 4.1 to 4.3%.\n\nGuinness Extra Cold is the exact same beer only served through a super cooler at 3.5 °C",
              "style": "Stout - Irish Dry",
              "custom_style": null,
              "original_style": "Stout - Irish Dry",
              "brewery": "Guinness",
              "custom_brewery": null,
              "original_brewery": "Guinness",
              "created_at": "2016-11-08T15:02:12Z",
              "updated_at": "2016-11-08T15:02:12Z"
            },
            {
              "id": 2,
              "section_id": 1,
              "position": 1,
              "untappd_id": 19004,
              "label_image": "https://untappd.akamaized.net/site/beer_logos/beer-bostonUtopias.jpg",
              "brewery_location": "Boston, MA",
              "abv": "27.0",
              "ibu": "0.0",
              "cask": false,
              "nitro": false,
              "tap_number": null,
              "rating": "4.47778",
              "in_production": true,
              "untappd_beer_slug": "boston-beer-company-samuel-adams-utopias",
              "untappd_brewery_id": 157,
              "name": "Samuel Adams Utopias",
              "original_name": "Samuel Adams Utopias",
              "custom_name": null,
              "description": "Truly the epitome of brewing\'s two thousand year evolution, Samuel Adams Utopias® offers a flavor not just unlike any other beer but unlike any other beverage in the world. Each release is a blend of batches, some having been aged up to 16 years in the barrel room of our Boston Brewery, in a variety of woods. We aged a portion of the beer in hand-selected, single-use bourbon casks from the award-winning Buffalo Trace Distillery. Some of the latest batches also spent time in Portuguese muscatel finishing casks, as well as sherry, brandy and Cognac casks. This flavorful, slightly fruity brew has a sweet, malty flavor that is reminiscent of a deep, rich vintage port, fine cognac or aged sherry.",
              "custom_description": null,
              "original_description": "Truly the epitome of brewing\'s two thousand year evolution, Samuel Adams Utopias® offers a flavor not just unlike any other beer but unlike any other beverage in the world. Each release is a blend of batches, some having been aged up to 16 years in the barrel room of our Boston Brewery, in a variety of woods. We aged a portion of the beer in hand-selected, single-use bourbon casks from the award-winning Buffalo Trace Distillery. Some of the latest batches also spent time in Portuguese muscatel finishing casks, as well as sherry, brandy and Cognac casks. This flavorful, slightly fruity brew has a sweet, malty flavor that is reminiscent of a deep, rich vintage port, fine cognac or aged sherry.",
              "style": "Strong Ale - American",
              "custom_style": null,
              "original_style": "Strong Ale - American",
              "brewery": "Boston Beer Company",
              "custom_brewery": null,
              "original_brewery": "Boston Beer Company",
              "created_at": "2016-11-08T15:01:54Z",
              "updated_at": "2016-11-08T15:01:54Z"
            }
          ]
        }'
    );

    return $responses[$request];
}
