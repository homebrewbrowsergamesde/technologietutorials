/* Copyright (C) 2012  Stephan Kreutzer
 *
 * This file is part of Tutorial "Text-Browsergames mit AJAX und PHP".
 *
 * Tutorial "Text-Browsergames mit AJAX und PHP" is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License version 3 only,
 * as published by the Free Software Foundation.
 *
 * Tutorial "Text-Browsergames mit AJAX und PHP" is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License 3 for more details.
 *
 * You should have received a copy of the GNU Affero General Public License 3
 * along with Tutorial "Text-Browsergames mit AJAX und PHP". If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * @file $/ttyajax.js
 * @author Stephan Kreutzer
 * @since 2012-06-19
 */



var xmlhttp = null;

// Mozilla
if (window.XMLHttpRequest)
{
    xmlhttp = new XMLHttpRequest();
}
// IE
else if (window.ActiveXObject)
{
    xmlhttp = new ActiveXObject('Microsoft.XMLHTTP');
}


{
    // Beim erstmaligen Oeffnen die Eingabezeile focusieren.
    // Moeglicherweise Browserabhaengig, hat im Test nicht funktioniert.

    var tty = document.getElementById('tty');

    if (tty != null)
    {
        tty.focus();
        tty.select();
    }
}



function ttyHandler()
{
    var tty = document.getElementById('tty');

    if (xmlhttp != null &&
        tty != null)
    {
        xmlhttp.open('POST', 'request.php', true);
        xmlhttp.setRequestHeader('Content-Type',
                                 'application/x-www-form-urlencoded');
        xmlhttp.onreadystatechange = resultHandler;
        xmlhttp.send('input=' + encodeURIComponent(tty.value));

        tty.value = '';
    }
    else
    {
        if (xmlhttp == null)
        {
            alert("Browser unterstützt kein AJAX.");
        }

        if (tty == null)
        {
            alert("Eingabefeld steht nicht zur Verfügung.");
        }
    }

    // Bricht das automatische Absenden des HTML-Formulars ab.
    return false;
}

function resultHandler()
{
    if (xmlhttp.readyState != 4)
    {
        // Warten...
    }

    if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
    {
        if (xmlhttp.responseText == '')
        {
            alert("Nicht angemeldet, keine Eingabe bereitgestellt oder unbekannter Fehler.");
            return;
        }

        var output = document.getElementById('output');

        if (output == null)
        {
            alert("Ausgabebereich steht nicht zur Verfügung.");
            return;
        }

        
        //alert(xmlhttp.responseText);
        var dom = xmlhttp.responseXML.documentElement;
        var div = document.createElement('div');

        var p = document.createElement('p');
        var content = document.createTextNode(dom.getElementsByTagName('input').item(0).firstChild.data);
        p.appendChild(content);
        div.appendChild(p);

        p = document.createElement('p');
        content = document.createTextNode(dom.getElementsByTagName('output').item(0).firstChild.data);
        p.appendChild(content);
        div.appendChild(p);

        output.appendChild(div);

        var tty = document.getElementById('tty');

        if (tty != null)
        {
            tty.value = '';
            window.scrollTo(0, window.outerHeight);
            tty.focus();
            // Notwendig?
            tty.select();
        }

        if (dom.getElementsByTagName('output').item(0).firstChild.data == "Beendet.")
        {
            var form = document.getElementById('form');

            if (form != null)
            {
                form.innerHTML = "<div>Hier <a href='index.php'>weiter</a>.</div>";
            }
        }
    }
    else if (xmlhttp.readyState == 4 && xmlhttp.status == 0)
    {
        var form = document.getElementById('form');

        if (form != null)
        {
            form.innerHTML = "<p>Offline...</p>";
        }
    }
}

