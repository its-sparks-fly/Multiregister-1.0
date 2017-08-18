<h1># Multiregister</h1>
Dieses Plugin erlaubt es euren Usern, ohne viel Aufwand neue Charaktere zu registrieren - und das direkt über das UserCP! Ganz ohne Ausloggen: einfach Charakternamen Passwort und falls nötig benötigte Profilfelder bei Registrierung angeben und los geht's. E-Mail-Adresse und Einstellungen werden automatisch vom aktiven Charakter übernommen. Je nachdem ob die Kooperation mit dem Accountswitcher-Plugin aktiviert ist, wird der neu erstellte Account direkt mit dem Master-Account des aktuellen Accounts (oder mit ihm, falls keiner vorhanden!) verknüpft. Folgende Einstellungen werden im AdminCP gemacht:

<ul>
<li> Accountswitcher-Kooperation aktivieren/deaktivieren
<li> Standardbenutzergruppe neu erstellter Accounts
</ul>

<h1>Plugin funktionsfähig machen</h1>
<ul>
<li>Die Plugin-Datei ladet ihr in den angegebenen Ordner <b>inc/plugins</b> hoch.
<li>Die Sprachdatei ladet ihr in den angegebenen Ordner <b>inc/languages/deutsch_du</b> hoch.
<li>Das Plugin muss nun im Admin CP unter <b>Konfiguration - Plugins</b> installiert und aktiviert werden
<li>In den Foreneinstellungen findet ihr nun - ganz unten - Einstellungen zu "Bewerber-Checklist". Macht dort eure Einstellungen.
</ul><br />

Das Plugin ist nun einsatzbereit.
In der Navigation des UserCPs findet sich ein Verweis auf die entsprechende Seite:
usercp.php?action=multiregister

<h1>Template-Änderungen</h1>
Folgende Templates werden durch dieses Plugin <i>neu hinzugefügt</i>:
<ul>
<li>usercp_multiregister
<li>usercp_multiregister_master
<li>usercp_nav_multiregister
</ul>

<h1>Demo</h1><br />
<center>
<img src="http://fs5.directupload.net/images/170818/5warz3bl.png" /><br />
http://fs5.directupload.net/images/170818/5warz3bl.png<br /><br />

<img src="http://fs5.directupload.net/images/170818/i8587dlo.png" /><br />
http://fs5.directupload.net/images/170818/i8587dlo.png<br /><br />

</center>

Das Plugin wurde unter der aktuellsten MyBB-Version und im aktiven Forengebrauch getestet. Bei ersten Tests sind <em>keine Fehler</em> unterlaufen; es empfiehlt sich trotz Allem, die Datenbank vor dem Installieren und erstmaligen Gebrauch zu sichern. 

