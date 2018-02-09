Make sure your system meets the following requirements:

* PHP 5.4+
* ExpressionEngine 3.0 or later
* A Client ID & Secret from the Rets Rabbit API [https://retsrabbit.com](https://retsrabbit.com). Contact Patrick Pohler contact@retsrabbit.com for details.

# Installation

Copy the `system/user/addons/rets_rabbit_v2` folder to your project's addon folder usually located at `system/user/addons`.

Install the module by going to **Developer > Add-On Manager**, look for Rets Rabbit and click **install**.

That's it! Go to the Rets Rabbit module's Control Panel (**Developer > Add-On Manager > Rets Rabbit**) to finish setting up the module

# Connecting to Rets Rabbit

To setup the module, you'll need to enter the **Client ID & Client Secret** for your Rets Rabbit Account on the module settings page.

Once you enter your ID & Secret and hit Submit you should be able to access the "Servers" and "Explore" tabs.

After you hit submit, you **MUST** click on the Servers tab, this will help you test your connection to Rets Rabbit, as well as prepare your installation with the registered servers on your RR account.

The Servers tab has a "Refresh from API" button to manually refresh the data from the Rets Rabbit API.

# Servers

The Servers tab will help you manage the real estate servers your account has access to. Each account will have at least one server. This tab will be populated automatically whenever you load the tab from the CP.

**Short Code**: This field can set to help you pull lisiting from multiple servers easily. For example, if you have a server you label as "new york" you'll just need to pass the parameter **short_code='new york'** to the Rets Rabbit EE tags.

**ID**: This is the unique id every real estate board's server has in the Rets Rabbit database. You cannot change this value, however it is useful if you need to interact with the RR API directly. Otherwise you can ignore this field.

**Default**: This setting determines the default server if no "short_code" parameter is passed. If you have multiple servers you can select any server listed as the default.

# Explore

The Explore tab will give you a way to examine and search the fields returned by the Rets Rabbit API for the servers your account has access to. The fetch button will pull one listing from the server you have selected.

Once listing data is returned by the API, you can use the search input to query against the listing fields. The search is case insensitive and doesn't search the listing values, just the keys.

# Module Tags
### {exp:rets_rabbit_v2:properties}

The  tag runs a query against the API to return and display property resource data.

#### Example Usage

```
{exp:rets_rabbit_v2:properties
    top="12"
    select="ListingId, ListPrice, OriginalListPrice, City, StateOrProvince, PublicRemarks, photos"
    orderby="ListPrice desc"
    filter="ListPrice gt 250000 and ListPrice lt 255000"
    cache="true"
    cache_duration="100"
    strip_tags="true"
    all="y"
}
    <div class="col-sm-6 col-md-4">
        <div class="panel panel-default">
            <div class="panel-heading">
                MLS ID {ListingId}
            </div>
            <div class="panel-body">
                <p>
                    Price: {ListPrice}
                </p>
                <p>
                    {PublicRemarks}
                </p>
            </div>
            <div class="panel-footer">
                <a class="btn btn-info" href="/properties/details?id={ListingId}">Details</a>
            </div>
        </div>
    </div>
{/exp:rets_rabbit_v2}
```

The tagpair parses the Property resources returned by the API, which you can use to build your real estate markup. There are several parameters available to you for tailoring your queries to the Rets Rabbit API.

#### Parameters

- `top` - Number of records to return.
- `select` - Specify which fields to return for each Property. Must be a comma separate list of fields.
- `orderby` - Specify the field and direction to order the results by.
- `filter` - Pass a Data Dictionary valid query string to filter the results. See the [RR Api docs](https://retsrabbit.com/docs) for more info
- `cache` - (Default no) Cache the results. Valid values: **y, yes, n, no**.
- `cache_duration` - (Default 60 minutes) Adjust cache duration in seconds.
- `strip_tags` - 
- `all` - 