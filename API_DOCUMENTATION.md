# Home Assistant Bridge API Documentation

## Overview
This API provides a simplified interface to control Home Assistant devices, designed for both human developers and AI-driven automation (LLaMA 3 integration).

**Base URL:** `http://localhost:8383/api`

---

## Authentication
Currently, the API handles Home Assistant authentication internally using environment variables. No additional authentication is required for API requests.

---

## Core Endpoints

### System Status

#### `GET /status`
Check API health and Home Assistant connectivity.

**Response (when connected):**
```json
{
  "api": "ok",
  "timestamp": "2026-03-19T08:06:01+00:00",
  "homeassistant": {
    "status": "connected",
    "entity_count": 411,
    "url": "http://homeassistant.local:8123"
  }
}
```

**Response (when not configured):**
```json
{
  "api": "ok",
  "timestamp": "2026-03-19T08:06:01+00:00",
  "homeassistant": {
    "status": "not_configured",
    "message": "Home Assistant URL or API key not set"
  }
}
```

**Response (when connection fails):**
```json
{
  "api": "ok",
  "timestamp": "2026-03-19T08:06:01+00:00",
  "homeassistant": {
    "status": "error",
    "message": "Connection refused",
    "url": "http://homeassistant.local:8123"
  }
}
```

**Home Assistant Status Values:**
- `connected`: Successfully connected to Home Assistant
- `not_configured`: Missing URL or API key in configuration
- `error`: Configuration exists but connection failed

---

## Discovery & Querying

### Get All Entities

#### `GET /entities`
Retrieve all Home Assistant entities with type classification.

**Response:**
```json
[
  {
    "entity_id": "light.kitchen",
    "type": "light",
    "state": "on",
    "attributes": {
      "friendly_name": "Kitchen Light",
      "brightness": 255
    }
  }
]
```

**Notes:**
- Each entity includes a `type` field extracted from the entity_id prefix
- Returns raw Home Assistant data with minimal processing

---

### Areas

#### `GET /areas`
List all configured areas/rooms.

**Response:**
```json
[
  {
    "area_id": "kitchen",
    "name": "Kitchen"
  }
]
```

#### `GET /areas/{area}/entities`
Get all entities in a specific area.

**Parameters:**
- `area` (string, path): Area name or identifier

**Response:**
```json
{
  "area": "kitchen",
  "count": 5,
  "entities": [
    {
      "entity_id": "light.kitchen_ceiling",
      "type": "light",
      "state": "on",
      "attributes": {
        "friendly_name": "Kitchen Ceiling Light"
      }
    }
  ]
}
```

**Use Cases:**
- Batch operations on all lights in a room
- Area-wide automation
- Room-specific queries

---

### Area-Based Batch Operations

Use the areas endpoint to perform batch operations on multiple devices in a room.

**Example: Turn on all lights in kitchen**
```bash
# 1. Get all entities in the area
GET /areas/kitchen/entities

# 2. Filter for lights (type === 'light')
# 3. Turn on each light
POST /lights/kitchen_ceiling/on
POST /lights/kitchen_counter/on
POST /lights/kitchen_island/on
```

**Example: Set brightness for all lights in bedroom**
```bash
# 1. Get all entities in the area
GET /areas/bedroom/entities

# 2. Filter for lights
# 3. Set brightness for each
POST /lights/bedroom_ceiling/brightness
{"brightness": 64}

POST /lights/bedroom_nightstand/brightness
{"brightness": 32}
```

**Notes:**
- The API doesn't provide a single endpoint for batch operations
- Implement batch logic in your application by iterating through filtered entities
- This approach gives fine-grained control over individual device responses
- Consider running operations in parallel for better performance

---

## Lights 💡

### List All Lights

#### `GET /lights`
Retrieve all light entities with simplified attributes.

**Response:**
```json
{
  "count": 3,
  "lights": [
    {
      "entity_id": "light.kitchen",
      "name": "Kitchen Light",
      "state": "on",
      "brightness": 255,
      "color_temp": null,
      "rgb_color": null
    }
  ]
}
```

**Key Fields:**
- `brightness`: 0-255 (null if not supported)
- `state`: "on" or "off"
- `color_temp`: Color temperature in mireds (null if not supported)
- `rgb_color`: RGB array [r, g, b] (null if not supported)

---

### Get Single Light

#### `GET /lights/{entityId}`
Get detailed information about a specific light.

**Parameters:**
- `entityId` (string, path): Light identifier without "light." prefix

**Example:**
```bash
GET /lights/kitchen
# Queries: light.kitchen
```

---

### Turn On Light

#### `POST /lights/{entityId}/on`
Turn on a light with optional brightness.

**Parameters:**
- `entityId` (string, path): Light identifier without "light." prefix
- `brightness` (integer, body, optional): Brightness level 0-255

**Request Body:**
```json
{
  "brightness": 128
}
```

**Response:**
```json
{
  "success": true,
  "entity_id": "light.kitchen",
  "action": "turned_on",
  "brightness": 128
}
```

**Examples:**
```bash
# Turn on at full brightness
POST /lights/kitchen/on

# Turn on at 50% brightness
POST /lights/kitchen/on
{"brightness": 128}
```

---

### Turn Off Light

#### `POST /lights/{entityId}/off`
Turn off a light.

**Response:**
```json
{
  "success": true,
  "entity_id": "light.kitchen",
  "action": "turned_off"
}
```

---

### Set Brightness

#### `POST /lights/{entityId}/brightness`
Set light brightness (automatically turns on if off).

**Request Body:**
```json
{
  "brightness": 200
}
```

**Response:**
```json
{
  "success": true,
  "entity_id": "light.kitchen",
  "action": "brightness_set",
  "brightness": 200
}
```

---

## Switches 🔌

### List All Switches

#### `GET /switches`
Retrieve all switch entities.

**Response:**
```json
{
  "count": 2,
  "switches": [
    {
      "entity_id": "switch.fan",
      "name": "Bedroom Fan",
      "state": "off"
    }
  ]
}
```

---

### Turn On Switch

#### `POST /switches/{entityId}/on`
Turn on a switch.

**Response:**
```json
{
  "success": true,
  "entity_id": "switch.fan",
  "action": "turned_on"
}
```

---

### Turn Off Switch

#### `POST /switches/{entityId}/off`
Turn off a switch.

**Response:**
```json
{
  "success": true,
  "entity_id": "switch.fan",
  "action": "turned_off"
}
```

---

## Sensors 📊

### List All Sensors

#### `GET /sensors`
Retrieve all sensor entities.

**Response:**
```json
{
  "count": 10,
  "sensors": [
    {
      "entity_id": "sensor.living_room_temperature",
      "name": "Living Room Temperature",
      "state": "22.5",
      "unit": "°C",
      "device_class": "temperature"
    }
  ]
}
```

---

### Battery Levels

#### `GET /sensors/battery`
Get battery status for all battery-powered devices.

**Response:**
```json
{
  "count": 5,
  "devices": [
    {
      "entity_id": "sensor.door_sensor_battery",
      "name": "Door Sensor",
      "battery_level": "85",
      "unit": "%"
    }
  ]
}
```

**Use Cases:**
- Monitor low battery devices
- Schedule battery replacement
- Alert when batteries are below threshold

---

### Temperature Sensors

#### `GET /sensors/temperature`
Get all temperature readings.

**Response:**
```json
{
  "count": 3,
  "sensors": [
    {
      "entity_id": "sensor.bedroom_temperature",
      "name": "Bedroom Temperature",
      "temperature": "21.2",
      "unit": "°C"
    }
  ]
}
```

**Use Cases:**
- Room-by-room temperature monitoring
- Climate control automation
- Energy optimization

---

### Humidity Sensors

#### `GET /sensors/humidity`
Get all humidity readings.

**Response:**
```json
{
  "count": 2,
  "sensors": [
    {
      "entity_id": "sensor.bathroom_humidity",
      "name": "Bathroom Humidity",
      "humidity": "65",
      "unit": "%"
    }
  ]
}
```

**Use Cases:**
- Ventilation control
- Mold prevention
- Comfort monitoring

---

## Climate Control 🌡️

### List Climate Devices

#### `GET /climate`
Retrieve all climate control devices (thermostats, HVAC).

**Response:**
```json
{
  "count": 1,
  "devices": [
    {
      "entity_id": "climate.living_room",
      "name": "Living Room Thermostat",
      "state": "heat",
      "current_temperature": 21.5,
      "target_temperature": 22.0,
      "humidity": 45,
      "hvac_mode": "heat"
    }
  ]
}
```

---

### Set Temperature

#### `POST /climate/{entityId}/temperature`
Set target temperature for a climate device.

**Request Body:**
```json
{
  "temperature": 22.5
}
```

**Response:**
```json
{
  "success": true,
  "entity_id": "climate.living_room",
  "action": "temperature_set",
  "temperature": 22.5
}
```

---

## Error Responses

All endpoints may return error responses in this format:

```json
{
  "error": "Error description"
}
```

**Common HTTP Status Codes:**
- `200`: Success
- `400`: Bad Request (missing or invalid parameters)
- `404`: Not Found (entity doesn't exist)
- `500`: Server Error (Home Assistant connection issues, configuration errors)

---

## Common Patterns

### Entity ID Format
Entity IDs follow the pattern: `{type}.{identifier}`

Examples:
- `light.kitchen`
- `switch.bedroom_fan`
- `sensor.temperature_living_room`

**API Shortcut:** Most control endpoints accept just the identifier part. The API automatically adds the type prefix.

```bash
# Both are equivalent:
POST /lights/kitchen/on
POST /lights/light.kitchen/on  # Also works
```

---

## Use Case Examples

### Morning Routine
```bash
# Get current temperature
GET /sensors/temperature

# Turn on kitchen lights at 50%
POST /lights/kitchen/on
{"brightness": 128}

# Turn on coffee maker
POST /switches/coffee_maker/on
```

### Energy Saving
```bash
# Get all bedroom entities
GET /areas/bedroom/entities

# Turn off all lights in bedroom (filter type==='light' and iterate)
POST /lights/bedroom_ceiling/off
POST /lights/bedroom_nightstand/off
POST /lights/bedroom_lamp/off
```

### Area Control
```bash
# List all areas
GET /areas

# Turn on all kitchen lights at 75% brightness
GET /areas/kitchen/entities
# Filter for lights, then:
POST /lights/kitchen_ceiling/on
{"brightness": 192}
POST /lights/kitchen_counter/on
{"brightness": 192}
```

### Climate Monitoring
```bash
# Check all temperature sensors
GET /sensors/temperature

# Check humidity levels
GET /sensors/humidity

# Adjust thermostat based on readings
POST /climate/living_room/temperature
{"temperature": 21.5}
```

### Battery Maintenance
```bash
# Check all battery levels
GET /sensors/battery

# Identify devices below 20%
# (Filter in application logic)
```

---

## AI Integration Notes

### Design Principles
1. **Semantic Clarity**: Endpoint names describe actions clearly (`/on`, `/off`, not `/toggle`)
2. **Consistent Responses**: All control endpoints return `success`, `entity_id`, and `action`
3. **Type Grouping**: Entities grouped by function (lights, sensors, switches)
4. **Simplified Data**: Only relevant attributes included in responses
5. **Predictable Patterns**: Similar entities have similar endpoints

### Training Recommendations
- Use GET endpoints for state queries
- Use POST endpoints for actions/control
- Entity names in responses match user vocabulary
- Consistent JSON structure for pattern recognition
- Error messages are descriptive

---

## Configuration

### Environment Variables
```env
HOME_ASSISTANT_URL=http://homeassistant.local:8123
HOME_ASSISTANT_API_KEY=your_long_lived_access_token
```

### Generating Access Token
1. Open Home Assistant web interface
2. Click on your profile (bottom left)
3. Scroll to "Long-Lived Access Tokens"
4. Click "Create Token"
5. Copy the token to `.env` file

---

## Implemented Features
- [x] Area discovery and entity grouping
- [x] Area-based batch operations (client-side implementation)
- [x] Type extraction from entity IDs
- [x] Status endpoint with connectivity check
- [x] Lights, switches, sensors, and climate control

## Future Enhancements
- [ ] Authentication/API keys for external access
- [ ] WebSocket support for real-time updates
- [ ] Scene activation endpoints
- [ ] Automation trigger endpoints
- [ ] Media player controls
- [ ] Cover/blind controls
- [ ] Lock controls
- [ ] Camera snapshots
- [ ] Historical data queries
- [ ] Server-side batch operation endpoint
