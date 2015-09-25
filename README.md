Cambusa / Corsaro v1.70
=======================

### © 2015 Rodolfo Calzetti

Web application framework (_Cambusa_)  
Web-based Enterprise Resource Planning (_Corsaro_)  
Content Management System (_Filibuster_)  

Have you any questions? You can email me at postmaster@rudyz.net

Visit my site [Le Cose di Rudy](http://www.rudyz.net)

---

The set includes:

1. framework __Cambusa__<br>

2. application __Corsaro__ based on Cambusa<br>
   * Maintenance<br>
   * Master Data<br>
   * Pi Cube (processes, dossiers, projects, ...)<br>
   * Mercante (orders and invoices)<br>
   * Pluto (financial instruments)<br>
   * Legend (invoice collection management)<br>

3. CMS __Filibuster__ based on Corsaro  
   An example of a website built by means of _Filibuster_: [No Boundaries](http://www.rudyz.net/apps/corsaro/filibuster.php?env=flb_giovyz&site=senzaconfini&id=A000000009019S)

---

Prerequisites
* __PHP >= 5.3__<br>
* __SQLite__ enabled (for default configuration only)<br>
  be sure that the following are enabled in php.ini<br>
  extension=php_sqlite.dll<br>
  extension=php_sqlite3.dll<br>

---

Getting started with Corsaro
============================

1. unzip the package in a web folder

2. open <code>\projects.php</code>

3. choose a name for the installation (_Project_), e.g. __demo__

4. enter the password __badwolf__ (you can change the password in the file <code>\xpassword.php</code>)

5. click <code>demo</code>

6. in the new page <code>Home Project DEMO</code>:<br>
   * click <code>Monad, Ego, Pulse and dictionaries</code><br>
   * click <code>Maestro</code> (user:__demiurge__, password:__sonoio__)<br>
     change the password and click <code>Go to application</code><br>
     on second tab <code>Upgrade</code>, choose <code>[demo]</code> and click <code>Create/Update</code><br>
     logout and close the page

7. on <code>Home Project DEMO</code> click <code>Corsaro</code><br>
   * click <code>Maintenance (folder) -> Options (item) -> Maintenance (tab)</code><br>
   * click <code>Refresh all views</code><br>

---

Features
========

1. __Lightweight Access to Databases__ ( [example](http://www.rudyz.net/apps/corsaro/filibuster.php?env=flb_scibile&site=matematica&id=A00000000K00CH) )<br>
The LAD protocol allows you to have the results of a query without actually running the query, getting the instant population of lists with tens of thousands of records.

2. __Front-end SPA+MDI__<br>
![MDI](https://raw.githubusercontent.com/cambusa/corsaro/master/screenshot01.png)  
A single page, but many independent windows dynamically loaded. Your application can grow indefinitely.

3. __Arrow-Oriented Modeling__<br>
   * _"Object"_ is any entity that has a master data "individuality": people, companies, offices, accounts, locations, ...<br>
   * _"Genre"_, instead, is an entity without individuality that is treated in amount.<br>
   * _"Motive"_ is the reason for which a genre is transferred.<br>
   * _"Arrow"_ is a transfer between two objects of a genre because a motive.<br>
   * _"Quiver"_ is a collection of arrows which describe a fully context. 

4. __Supported databases__
   * Oracle
   * SQL Server
   * MySQL
   * DB2
   * SQLite

5. __Central Authentication Service__  
   * double protected password by RSA+SHA1 encryption  
   * users  
   * applications  
   * roles (which are associated with customized menu in Corsaro)  
   * sessions  

---

License
=======

__GNU Lesser General Public License v3__

Build your web application with _Cambusa_ and deliver it with the license that you want!

---
