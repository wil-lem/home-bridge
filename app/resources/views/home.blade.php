@extends('layout')

@section('title', 'API Testing')

@section('content')
<div class="card" style="padding: 12px;">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <strong style="font-size: 15px;">Status</strong>
        <div id="status-display" style="font-size: 13px;">
            <p>Loading...</p>
        </div>
    </div>
</div>

<div class="grid">
    <div class="card">
        <h2>💡 Lights</h2>
        <button class="btn btn-primary" onclick="apiCall('GET', '/api/lights')">Get All</button>
        
        <h3>Control</h3>
        <input type="text" id="light-id" placeholder="Light ID (e.g., kitchen)" style="margin-bottom: 5px;">
        <div>
            <button class="btn btn-success" onclick="controlLight('on')">On</button>
            <button class="btn btn-danger" onclick="controlLight('off')">Off</button>
        </div>
        <input type="number" id="light-brightness" min="0" max="255" value="128" placeholder="Brightness (0-255)" style="margin-top: 5px;">
        <button class="btn btn-primary" onclick="setBrightness()">Set Brightness</button>
    </div>

    <div class="card">
        <h2>🔌 Switches</h2>
        <button class="btn btn-primary" onclick="apiCall('GET', '/api/switches')">Get All</button>
        
        <h3>Control</h3>
        <input type="text" id="switch-id" placeholder="Switch ID (e.g., fan)" style="margin-bottom: 5px;">
        <div>
            <button class="btn btn-success" onclick="controlSwitch('on')">On</button>
            <button class="btn btn-danger" onclick="controlSwitch('off')">Off</button>
        </div>
    </div>

    <div class="card">
        <h2>📊 Sensors</h2>
        <button class="btn btn-primary" onclick="apiCall('GET', '/api/sensors')">All</button>
        <button class="btn btn-primary" onclick="apiCall('GET', '/api/sensors/temperature')">Temp</button>
        <button class="btn btn-primary" onclick="apiCall('GET', '/api/sensors/humidity')">Humidity</button>
        <button class="btn btn-primary" onclick="apiCall('GET', '/api/sensors/battery')">Battery</button>
    </div>

    <div class="card">
        <h2>🌡️ Climate</h2>
        <button class="btn btn-primary" onclick="apiCall('GET', '/api/climate')">Get All</button>
        
        <h3>Set Temp</h3>
        <input type="text" id="climate-id" placeholder="Climate ID (e.g., living_room)" style="margin-bottom: 5px;">
        <input type="number" id="climate-temp" step="0.5" value="21" placeholder="Temperature (°C)" style="margin-bottom: 5px;">
        <button class="btn btn-primary" onclick="setTemperature()">Set Temperature</button>
    </div>
</div>

<div class="card">
    <h2>🔍 Discovery</h2>
    <button class="btn btn-primary" onclick="apiCall('GET', '/api/entities')">All Entities</button>
    <button class="btn btn-primary" onclick="apiCall('GET', '/api/areas')">All Areas</button>
</div>

<div class="card">
    <h2>🏠 Area Control</h2>
    <input type="text" id="area-name" placeholder="Area name (e.g., kitchen, bedroom)" style="margin-bottom: 5px;">
    <div style="margin-bottom: 8px;">
        <button class="btn btn-primary" onclick="getAreaEntities()">Get Area Entities</button>
    </div>
    <h3>Control All Lights in Area</h3>
    <div>
        <button class="btn btn-success" onclick="controlAreaLights('on')">All Lights On</button>
        <button class="btn btn-danger" onclick="controlAreaLights('off')">All Lights Off</button>
    </div>
    <input type="number" id="area-brightness" min="0" max="255" value="128" placeholder="Brightness (0-255)" style="margin-top: 5px;">
    <button class="btn btn-primary" onclick="setAreaBrightness()">Set All Brightness</button>
</div>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
        <h2 style="margin: 0;">API Request</h2>
        <button class="btn btn-primary" onclick="copyToClipboard('request-display')" style="padding: 5px 12px; font-size: 11px;">Copy</button>
    </div>
    <div id="request-display" class="response-box" style="font-size: 11px;">
        Make an API call to see the request here...
    </div>
</div>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
        <h2 style="margin: 0;">Response</h2>
        <button class="btn btn-primary" onclick="copyToClipboard('response-display')" style="padding: 5px 12px; font-size: 11px;">Copy</button>
    </div>
    <div id="response-display" class="response-box" style="font-size: 11px;">
        Make an API call to see the response here...
    </div>
</div>
@endsection

@section('scripts')
<script>
// Load status on page load
document.addEventListener('DOMContentLoaded', function() {
    loadStatus();
});

async function loadStatus() {
    try {
        const response = await fetch('/api/status');
        const data = await response.json();
        
        let statusHtml = '<div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">';
        
        // API Status
        statusHtml += '<div style="display: flex; align-items: center;">';
        statusHtml += '<span class="status-indicator status-ok"></span>';
        statusHtml += '<span>API: ' + data.api + '</span>';
        statusHtml += '</div>';
        
        // Home Assistant Status
        if (data.homeassistant) {
            const ha = data.homeassistant;
            let statusClass = 'status-ok';
            if (ha.status === 'error') statusClass = 'status-error';
            if (ha.status === 'not_configured') statusClass = 'status-warning';
            
            statusHtml += '<div style="display: flex; align-items: center;">';
            statusHtml += '<span class="status-indicator ' + statusClass + '"></span>';
            statusHtml += '<span>HA: ' + ha.status;
            if (ha.entity_count) {
                statusHtml += ' (' + ha.entity_count + ')';
            }
            statusHtml += '</span>';
            statusHtml += '</div>';
        }
        
        statusHtml += '</div>';
        
        if (data.homeassistant && data.homeassistant.message) {
            statusHtml += '<div style="margin-top: 8px; color: #e74c3c; font-size: 12px;">' + data.homeassistant.message + '</div>';
        }
        
        document.getElementById('status-display').innerHTML = statusHtml;
    } catch (error) {
        document.getElementById('status-display').innerHTML = 
            '<span class="status-indicator status-error"></span>Error: ' + error.message;
    }
}

function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    const text = element.textContent;
    
    navigator.clipboard.writeText(text).then(function() {
        // Show temporary success message
        const originalText = event.target.textContent;
        event.target.textContent = '✓ Copied!';
        event.target.style.background = '#27ae60';
        
        setTimeout(function() {
            event.target.textContent = originalText;
            event.target.style.background = '';
        }, 2000);
    }).catch(function(err) {
        alert('Failed to copy: ' + err);
    });
}

function displayRequest(method, endpoint, body = null) {
    let requestText = method + ' ' + endpoint;
    
    if (body) {
        requestText += '\n\nRequest Body:\n' + JSON.stringify(body, null, 2);
    }
    
    requestText += '\n\nFull cURL command:\n';
    requestText += 'curl -X ' + method + ' http://localhost:8383' + endpoint;
    
    if (body) {
        requestText += ' \\\n  -H "Content-Type: application/json" \\\n  -d \'' + JSON.stringify(body) + '\'';
    }
    
    document.getElementById('request-display').textContent = requestText;
}

async function apiCall(method, endpoint, body = null) {
    const responseBox = document.getElementById('response-display');
    
    // Display the request first
    displayRequest(method, endpoint, body);
    
    responseBox.textContent = 'Loading...';
    
    try {
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            }
        };
        
        if (body) {
            options.body = JSON.stringify(body);
        }
        
        const response = await fetch(endpoint, options);
        const data = await response.json();
        
        responseBox.textContent = JSON.stringify(data, null, 2);
    } catch (error) {
        responseBox.textContent = 'Error: ' + error.message;
    }
}

function controlLight(action) {
    const lightId = document.getElementById('light-id').value;
    if (!lightId) {
        alert('Please enter a light ID');
        return;
    }
    apiCall('POST', `/api/lights/${lightId}/${action}`);
}

function setBrightness() {
    const lightId = document.getElementById('light-id').value;
    const brightness = parseInt(document.getElementById('light-brightness').value);
    
    if (!lightId) {
        alert('Please enter a light ID');
        return;
    }
    
    apiCall('POST', `/api/lights/${lightId}/brightness`, { brightness: brightness });
}

function controlSwitch(action) {
    const switchId = document.getElementById('switch-id').value;
    if (!switchId) {
        alert('Please enter a switch ID');
        return;
    }
    apiCall('POST', `/api/switches/${switchId}/${action}`);
}

function setTemperature() {
    const climateId = document.getElementById('climate-id').value;
    const temperature = parseFloat(document.getElementById('climate-temp').value);
    
    if (!climateId) {
        alert('Please enter a climate device ID');
        return;
    }
    
    apiCall('POST', `/api/climate/${climateId}/temperature`, { temperature: temperature });
}

function getAreaEntities() {
    const areaName = document.getElementById('area-name').value;
    if (!areaName) {
        alert('Please enter an area name');
        return;
    }
    apiCall('GET', `/api/areas/${areaName}/entities`);
}

async function controlAreaLights(action) {
    const areaName = document.getElementById('area-name').value;
    if (!areaName) {
        alert('Please enter an area name');
        return;
    }
    
    const responseBox = document.getElementById('response-display');
    const requestBox = document.getElementById('request-display');
    
    try {
        responseBox.textContent = 'Loading area entities...';
        
        // First, get all entities in the area
        const entitiesResponse = await fetch(`/api/areas/${areaName}/entities`);
        const entitiesData = await entitiesResponse.json();
        
        if (!entitiesData.entities || entitiesData.entities.length === 0) {
            responseBox.textContent = `No entities found in area: ${areaName}`;
            requestBox.textContent = `GET /api/areas/${areaName}/entities\n\nNo entities found.`;
            return;
        }
        
        // Filter for lights only
        const lights = entitiesData.entities.filter(e => e.type === 'light');
        
        if (lights.length === 0) {
            responseBox.textContent = `No lights found in area: ${areaName}`;
            requestBox.textContent = `GET /api/areas/${areaName}/entities\n\nNo lights found in this area.`;
            return;
        }
        
        // Build request display
        let requestText = `Controlling ${lights.length} lights in area: ${areaName}\n\n`;
        lights.forEach(light => {
            const lightId = light.entity_id.split('.')[1];
            requestText += `POST /api/lights/${lightId}/${action}\n`;
        });
        requestBox.textContent = requestText;
        
        responseBox.textContent = `Turning ${action} ${lights.length} lights...`;
        
        // Turn on/off each light
        const results = [];
        for (const light of lights) {
            const lightId = light.entity_id.split('.')[1];
            const response = await fetch(`/api/lights/${lightId}/${action}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            });
            const data = await response.json();
            results.push(data);
        }
        
        responseBox.textContent = JSON.stringify({
            area: areaName,
            action: action,
            lights_controlled: lights.length,
            results: results
        }, null, 2);
        
    } catch (error) {
        responseBox.textContent = 'Error: ' + error.message;
    }
}

async function setAreaBrightness() {
    const areaName = document.getElementById('area-name').value;
    const brightness = parseInt(document.getElementById('area-brightness').value);
    
    if (!areaName) {
        alert('Please enter an area name');
        return;
    }
    
    const responseBox = document.getElementById('response-display');
    const requestBox = document.getElementById('request-display');
    
    try {
        responseBox.textContent = 'Loading area entities...';
        
        // Get all entities in the area
        const entitiesResponse = await fetch(`/api/areas/${areaName}/entities`);
        const entitiesData = await entitiesResponse.json();
        
        if (!entitiesData.entities || entitiesData.entities.length === 0) {
            responseBox.textContent = `No entities found in area: ${areaName}`;
            return;
        }
        
        // Filter for lights only
        const lights = entitiesData.entities.filter(e => e.type === 'light');
        
        if (lights.length === 0) {
            responseBox.textContent = `No lights found in area: ${areaName}`;
            return;
        }
        
        // Build request display
        let requestText = `Setting brightness for ${lights.length} lights in area: ${areaName}\n\n`;
        lights.forEach(light => {
            const lightId = light.entity_id.split('.')[1];
            requestText += `POST /api/lights/${lightId}/brightness\nBody: {"brightness": ${brightness}}\n\n`;
        });
        requestBox.textContent = requestText;
        
        responseBox.textContent = `Setting brightness for ${lights.length} lights...`;
        
        // Set brightness for each light
        const results = [];
        for (const light of lights) {
            const lightId = light.entity_id.split('.')[1];
            const response = await fetch(`/api/lights/${lightId}/brightness`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ brightness: brightness })
            });
            const data = await response.json();
            results.push(data);
        }
        
        responseBox.textContent = JSON.stringify({
            area: areaName,
            action: 'set_brightness',
            brightness: brightness,
            lights_controlled: lights.length,
            results: results
        }, null, 2);
        
    } catch (error) {
        responseBox.textContent = 'Error: ' + error.message;
    }
}
</script>
@endsection
