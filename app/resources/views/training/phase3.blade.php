@extends('layout')

@section('title', 'Phase 3: Multi-Step Reasoning')

@section('content')
<div class="card">
    <h2>Phase 3: Multi-Step Reasoning (Advanced)</h2>
    <p><strong>Duration:</strong> Week 5-6</p>
    <p><strong>Objective:</strong> Enable complex scenarios requiring multiple API calls.</p>
</div>

<div class="card">
    <h2>Scenario-Based Training</h2>
    
    <h3>Morning Routine</h3>
    <pre><code>{
  "instruction": "Start my morning routine",
  "reasoning": "Morning routine typically involves: checking weather, turning on lights, starting coffee",
  "output": [
    "GET /api/sensors/temperature",
    "POST /api/lights/kitchen/on with {\"brightness\": 200}",
    "POST /api/lights/bedroom/on with {\"brightness\": 100}",
    "POST /api/switches/coffee_maker/on"
  ],
  "response": "Good morning! The temperature is 18°C. I've turned on the kitchen and bedroom lights and started the coffee maker."
}</code></pre>

    <h3>Energy Saving (Leaving House)</h3>
    <pre><code>{
  "instruction": "I'm leaving the house, save energy",
  "reasoning": "Turn off lights, adjust thermostat, turn off non-essential switches",
  "output": [
    "GET /api/lights (to find all on lights)",
    "For each on light: POST /api/lights/{id}/off",
    "POST /api/climate/living_room/temperature with {\"temperature\": 19}",
    "POST /api/switches/coffee_maker/off"
  ],
  "response": "I've turned off 5 lights, set the thermostat to 19°C, and turned off the coffee maker. Have a great day!"
}</code></pre>

    <h3>Comfort Optimization</h3>
    <pre><code>{
  "instruction": "Make the bedroom comfortable for sleep",
  "reasoning": "Dim lights, adjust temperature, check humidity",
  "output": [
    "POST /api/lights/bedroom/brightness with {\"brightness\": 10}",
    "POST /api/climate/bedroom/temperature with {\"temperature\": 18}",
    "GET /api/sensors/humidity"
  ],
  "response": "I've dimmed the bedroom light to 10%, set the temperature to 18°C. Current humidity is 55%."
}</code></pre>
</div>

<div class="card">
    <h2>Custom Routines</h2>
    <p>Examples of user-defined multi-step routines:</p>
    
    <h3>Movie Time</h3>
    <pre><code>{
  "instruction": "Start movie mode",
  "actions": [
    "POST /api/lights/living_room/brightness {\"brightness\": 30}",
    "POST /api/lights/ambient/on {\"brightness\": 50}",
    "POST /api/climate/living_room/temperature {\"temperature\": 20}"
  ]
}</code></pre>

    <h3>Bedtime</h3>
    <pre><code>{
  "instruction": "Goodnight",
  "actions": [
    "Turn off all lights except bedroom nightstand (dim)",
    "Set thermostat to sleep temperature",
    "Lock doors (if available)",
    "Check all windows are closed"
  ]
}</code></pre>
</div>

<div class="card">
    <h2>Validation Criteria</h2>
    <ul style="margin-left: 20px; line-height: 1.8;">
        <li>Correctly executes multi-step routines >90% of the time</li>
        <li>Sequences actions in logical order</li>
        <li>Provides comprehensive feedback about all actions taken</li>
        <li>Handles failures gracefully (e.g., one light fails, continues with others)</li>
    </ul>
</div>

<div style="display: flex; justify-content: space-between; margin-top: 20px;">
    <a href="/training/phase2" class="btn btn-primary">← Phase 2</a>
    <a href="/training/phase4" class="btn btn-primary">Phase 4 →</a>
</div>
@endsection
