# Rets Rabbit EE3/4 Plugin

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
### `{exp:rets_rabbit_v2:properties}`

The  tag runs a query against the API to return and display property resource data.

#### Example Usage

```
{exp:rets_rabbit_v2:properties
    top="12"
    select="ListingId, ListPrice, OriginalListPrice, City, StateOrProvince, PublicRemarks, photos"
    orderby="ListPrice desc"
    filter="ListPrice gt 250000 and ListPrice lt 255000"
    cache="y"
    cache_duration="100"
    strip_tags="y"
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

- `top` - (optional) Number of records to return.
- `select` - (optional) Specify which fields to return for each Property. Must be a comma separate list of fields.
- `orderby` - (optional) Specify the field and direction to order the results by.
- `filter` - (optional) Pass a Data Dictionary valid query string to filter the results. See the [Rets Rabbit API docs](https://retsrabbit.com/docs/v2) for more info
- `short_code` - (optional) Specify a specific server to query against. Useful if you have more than one server on your account.
- `cache` - (optional, default: no) Cache the results. Possible values: **y, yes, n, no**.
- `cache_duration` - (optional, default: 60 minutes) Adjust cache duration in seconds.
- `strip_tags` - (optional, default: no) Strip HTML tags from the results Possible values: **y, yes, n, no**.
- `all` - (optional, default: no) Specify whether to query against all of your available servers or not. Ignored if `short_code` has been supplied. Possible values: **y, yes, n, no**.

### `{exp:rets_rabbit_v2:property}`

Use this tag to fetch a single property resource by mls_id.

#### Example Usage

```
{exp:rets_rabbit_v2:property
    mls_id="{get:id}"
    strip_tags="y"
    select="ListingId, ListPrice, PublicRemarks, City, StateOrProvince"
}
    <div class="row">
         <div class="col-sm-8 col-sm-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">
                    {ListingId}
                </div>
                <div class="panel-body">
                    {PublicRemarks}
                </div>
            </div>
        </div>
    </div>
{/exp:rets_rabbit_v2:property}
```

#### Parameters

- `mls_id` - (required) The id of the property resource you are trying to fetch.
- `select` - (optional) Specifically request only a certain subset of available fields
- `short_code` - (optional) Specify a specific server to query against. Useful if you have more than one server on your account.
- `cache` - (optional, default: no) Cache the results. Valid values: **y, yes, n, no**.
- `cache_duration` - (optional, default: 60 minutes) Adjust cache duration in seconds.
- `strip_tags` - (optional, default: no) Strip HTML tags from the results.

### `{exp:rets_rabbit_v2:search_form}`

While the `{exp:rets_rabbit_v2:properties}` tag is great for fetching properties with static queries, sometimes you want to offer your users a search form where they can run their own searches.

#### Example Usage

```
{exp:rets_rabbit_v2:search_form
        form_id="search"
        form_class=""
        all="y"
        results_path="/properties/results?search=:search_id:"
    }
        <div class="form-group">
            <input type="text" name="rr:StateOrProvince/City/PostalCode-contains-" class="form-control" placeholder="State, City, Zip..." id="wildcard-search">
        </div>
        <div class="form-group">
            <div class="row">
                <div class="col-sm-6">
                    <select name="rr:ListPrice-ge-" id="min-price" class="form-control">
                        <option value>-- Min Price --</option>
                        <option value="10000">$10,000</option>
                        <option value="30000">$30,000</option>
                        <option value="50000">$50,000</option>
                        <option value="70000">$70,000</option>
                        <option value="90000">$90,000</option>
                        <option value="110000">$110,000</option>
                        <option value="150000">$150,000</option>
                        <option value="200000">$200,000</option>
                        <option value="250000">$250,000</option>
                        <option value="300000">$300,000</option>
                    </select>
                </div>
                <div class="col-sm-6">
                    <select name="rr:ListPrice-le-" id="max-price" class="form-control">
                        <option value>-- Max Price --</option>
                        <option value="100000">$100,000</option>
                        <option value="150000">$150,000</option>
                        <option value="200000">$200,000</option>
                        <option value="250000">$250,000</option>
                        <option value="300000">$300,000</option>
                        <option value="350000">$350,000</option>
                        <option value="400000">$400,000</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <div class="col-sm-6">
                    <select name="rr:BedroomsTotal-ge-" id="bedrooms" class="form-control">
                        <option value>-- Bedrooms --</option>
                        <option value="1">1+</option>
                        <option value="2">2+</option>
                        <option value="3">3+</option>
                        <option value="4">4+</option>
                        <option value="5">5+</option>
                    </select>
                </div>
                <div class="col-sm-6">
                    <select name="rr:BathroomsFull-ge-" id="bedrooms" class="form-control">
                        <option value>-- Bathrooms --</option>
                        <option value="1">1+</option>
                        <option value="2">2+</option>
                        <option value="3">3+</option>
                        <option value="4">4+</option>
                        <option value="5">5+</option>
                    </select>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-success">Search</button>
    {/exp:rets_rabbit_v2:search_form}
```

#### Parameters

- `form_id` - (required) Give your form an id.
- `results_path` - (required) Specify the url to display the search results.
- `form_class` - (optional) Specify the class or classes for this form.
- `short_code` - (optional) Specify a specific server to query against. Useful if you have more than one server on your account.
- `all` - (optional, default: no) Specify whether to query against all of your available servers or not. Ignored if `short_code` has been supplied.

**`results_path`** - When processing the search form, this module will try to build the `results_path` using one of two ways. The first method is the most flexible and what we recommend you to use.

**Method 1**

In this example there is a special term in the `results_path` tag argument called `:search_id:`. If the module finds this term it will replace it with the id of the newly created search.

```
{exp:rets_rabbit_v2:search_form
    form_id="search"
    all="y"
    results_path="/properties/results?search=:search_id:"
}
{/exp:rets_rabbit_v2:search_form}
```

**Method 2**

In this example we just pass a simple string argument to the `results_path` tag argument. If the module can't find the `:search_id:` term, then it assumes this method is being used to set the results path. The module will take the supplied results path and append the new search id to it.

```
{exp:rets_rabbit_v2:search_form
    form_id="search"
    all="y"
    results_path="/properties/results"
}
{/exp:rets_rabbit_v2:search_form}
```

#### Search Form DSL

To give you as the developer the flexibility to craft search forms that cover a wide range of query types, we have provided a domain specific language to use inside the `{exp:rets_rabbit_v2:search_form}` tag. When writing your search form markup, you will enter the name of the property field you want to search against in the `name` attribute of your input. The name of the property field must be prefixed with `rr:` so that the module knows to parse it. The syntax specifically looks like the following:

`<input name="rr:{fieldName1/fieldName2/fieldName3/fieldName{n}}-{operator}-" value="">`

Because of how strictly EE scrubs form input names, we had to come up with the syntax you see above. In a CMS such as Craft, the same input field would look like the following:

`<input name="rr:{fieldName1|fieldName2|fieldName3|fieldName{n}}({operator})" value="">`

We feel that the above syntax is more elegant and easier to read, and so we've included it to help you get a better understanding of how to write your search form markup.

These operators can be used when crafting your search form:

1. eq
2. lt
3. gt
4. le
5. ge
6. ne
7. contains
8. endswith
9. starswith
10. between

There are three types of queries which make up the foundation for most queries you would need to prepare for in your search forms.

1. Searching against multiple fields for a single value
2. Searching against a single field for a single value
3. Searching against a single field for multiple values

**Multiple Fields / Single Value**

`<input name="rr:StateOrProvince/PostalCode/City-eq-" value="columbus">`

The above input will create an ODATA partial filter which looks like the following: 

`(StateOrProvince eq 'columbus' or PostalCode eq 'columbus' or City eq 'columbus')`

This type of input is particulary useful when you want to have a main search bar perhaps in the banner of your home page or at the top of a search form.

**Single Field / Single Value**

`<input name="rr:StateOrProvince" value="columbus">`

The above input will generate the following ODATA partial filter:

`StateOrProvince eq 'columbus'`

**Single Field / Multiple Values**

`<input name="rr:PostalCode-ge-[]" type="checkbox" value="">`

If we had say, 5 checkboxes of which 3 were checked when the form was submitted, the following partial filter would be generated:

`(PostalCode eq 'val1' or PostalCode eq 'val2' or PostalCode eq 'val3')`

### `{exp:rets_rabbit_v2:search_results}`

This tag is used to display results from a search form.

#### Example Usage

```
{exp:rets_rabbit_v2:search_results
    select="ListingId, ListPrice, PublicRemarks, City, StateOrProvince"
    orderby="ListPrice desc"
    per_page="15"
    search_id="{get:search}"
}
    {if no_results}
        {if has_error}
            <div class="col-sm-12">
                <div class="alert alert-warning">
                    <p>Unable to run your search</p>
                </div>
            </div>
        {if:else}
            <div class="col-sm-12">
                <div class="alert alert-warning">
                    <p>No results found for your search</p>
                </div>
            </div>
        {/if}
    {/if}

        
    <div class="col-sm-4">
        <div class="panel panel-default">
            <div class="panel-heading">
                MLS ID {ListingId}
            </div>
            <div class="panel-body">
                <div>
                    Price: {ListPrice}
                </div>
            </div>
            <div class="panel-footer">
                <a class="btn btn-info" href="/properties/details?id={ListingId}">Details</a>
            </div>
        </div>
    </div>

    {paginate}
        {pagination_links}
            <ul class="pagination">
                {first_page}
                    <li><a href="{pagination_url}?search={get:search}" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>
                {/first_page}
                {page}
                    <li{if current_page} class="active"{/if}><a href="{pagination_url}?search={get:search}">{pagination_page_number}</a></li>
                {/page}
                {last_page}
                    <li><a href="{pagination_url}?search={get:search}" aria-label="Previous"><span aria-hidden="true">&raquo;</span></a></li>
                {/last_page}
            </ul>
        {/pagination_links}
    {/paginate}
{/exp:rets_rabbit_v2}
```

#### Parameters

- `search_id` - (Required) Pass in 
- `per_page` - Specify how many items per page
- `select` - Specify which fields to return for each Property. Must be a comma separate list of fields.
- `orderby` - Specify the field and direction to order the results by.
- `cache` - (Default, no) Cache the results. Possible values: **y, yes, n, no**.
- `cache_duration` - (Default 60 minutes) Adjust cache duration in seconds.
- `strip_tags` - (Default, no) Strip HTML tags from the results Possible values: **y, yes, n, no**.
- `count` - (Default, estimated) Set the type of counting method to be used on the result set. Possible values: **estimated, exact**. `Estimated` counts will be returned much faster but will not be as accurate as `exact` counts.

