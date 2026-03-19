@extends('layout')

@section('title', 'Phase 4: State-Aware Decisions')

@section('content')
<div class="card">
    <h2>Phase 4: State-Aware Decisions (Expert)</h2>
    <p><strong>Duration:</strong> Week 7-8</p>
    <p><strong>Objective:</strong> Make intelligent decisions based on current state.</p>
</div>

<div class="card">
    <h2>Conditional Logic Examples</h2>
    
    <h3>State Checking Before Action</h3>
    <pre><code>{
  "instruction": "Turn on the light if it's off",
  "logic": [
    "1. GET /api/lights/{id} to check current state",
    "2. If state === 'off': POST /api/lights/{id}/on",
    "3. Else: respond 'The light is already on'"
  ],
  "example_response": "Checked the kitchen light - it's already on, no action needed."
}</code></pre>

    <h3>Batch State Management</h3>
    <pre><code>{
  "instruction": "Make sure all lights are off",
  "logic": [
    "1. GET /api/lights",
    "2. Filter lights where state === 'on'",
    "3. For each: POST /api/lights/{id}/off",
    "4. Respond with count of lights turned off"
  ],
  "example_response": "Found 3 lights still on. I've turned them all off."
}</code></pre>

    <h3>Smart Monitoring</h3>
    <pre><code>{
  "instruction": "Alert me if any battery is low",
  "logic": [
    "1. GET /api/sensors/battery",
    "2. Filter devices where battery_level < 20",
    "3. Return list of devices needing battery replacement"
  ],
  "example_response": "Warning: 2 devices have low battery:\n- Door Sensor (15%)\n- Motion Sensor Hall (10%)"
}</code></pre>
</div>

<div class="card">
    <h2>Intelligent Recommendations</h2>
    
    <h3>Temperature-Based Suggestions</h3>
    <pre><code>{
  "instruction": "Am I wasting energy?",
  "logic": [
    "1. GET /api/sensors/temperature (check all rooms)",
    "2. GET /api/climate (check thermostats)",
    "3. GET /api/lights (check which are on)",
    "4. Analyze patterns and suggest improvements"
  ],
  "example_response": "Energy analysis:\n- Living room is heated to 22°C but temperature sensor shows 24°C\n- 3 lights on in empty rooms\n- Suggests: Lower thermostat by 2°C, turn off unused lights"
}</code></pre>

    <h3>Comfort Optimization</h3>
    <pre><code>{
  "instruction": "Optimize my bedroom for sleep",
  "logic": [
    "1. GET current temperature, humidity, light levels",
    "2. Compare with optimal sleep conditions (18-20°C, 40-60% humidity, minimal light)",
    "3. Make adjustments as needed",
    "4. Report what was changed and why"
  ],
  "example_response": "Sleep optimization:\n- Temperature: 22°C → adjusted to 19°C (optimal for sleep)\n- Humidity: 58% (perfect, no change needed)\n- Lights: Dimmed to 5% brightness\n- Your bedroom is now optimized for sleep."
}</code></pre>
</div>

<div class="card">
    <h2>Error Recovery</h2>
    
    <h3>Handling Device Unavailability</h3>
    <pre><code>{
  "instruction": "Turn on all bedroom lights",
  "logic": [
    "1. Try to turn on each light",
    "2. If one fails, continue with others",
    "3. Report successes and failures"
  ],
  "example_response": "Bedroom lights:\n✓ Ceiling light: turned on\n✓ Nightstand lamp: turned on\n✗ Desk lamp: unavailable (device offline)\n2 of 3 lights are now on."
}</code></pre>
</div>

<div class="card">
    <h2>Advanced Pattern Recognition</h2>
    <p>Train the model to recognize user patterns and make proactive suggestions:</p>
    
    <ul style="margin-left: 20px; line-height: 1.8;">
        <li>Notice if user always turns on certain lights together → suggest creating a routine</li>
        <li>Detect unusual energy usage patterns → alert user</li>
        <li>Learn preferred temperature settings by time of day</li>
        <li>Identify devices that haven't been used in a long time</li>
    </ul>
</div>

<div class="card">
    <h2>Validation Criteria</h2>
    <ul style="margin-left: 20px; line-height: 1.8;">
        <li>Checks current state before making changes >85% of the time when appropriate</li>
        <li>Provides intelligent recommendations based on sensor data</li>
        <li>Handles device failures gracefully</li>
        <li>Explains reasoning behind decisions</li>
        <li>Adapts responses based on actual conditions</li>
    </ul>
</div>

<div class="card">
    <h2>Completion</h2>
    <p>Once you achieve >85% accuracy on Phase 4, your AI assistant is ready for production use! Continue monitoring and improving based on real-world usage.</p>
    
    <h3>Post-Training Tasks</h3>
    <ul style="margin-left: 20px; line-height: 1.8;">
        <li>Deploy to production environment</li>
        <li>Set up logging and monitoring</li>
        <li>Collect user feedback</li>
        <li>Retrain monthly with new examples</li>
        <li>Expand to new device types as needed</li>
    </ul>
</div>

<div style="display: flex; justify-content: space-between; margin-top: 20px;">
    <a href="/training/phase3" class="btn btn-primary">← Phase 3</a>
    <a href="/training/overview" class="btn btn-primary">Back to Overview</a>
</div>
@endsection
