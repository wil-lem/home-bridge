@extends('layout')

@section('title', 'Phase 2: Context Awareness')

@section('content')
<div class="card">
    <h2>Phase 2: Context Awareness (Intermediate)</h2>
    <p><strong>Duration:</strong> Week 3-4</p>
    <p><strong>Objective:</strong> Teach the model to handle ambiguity and maintain context.</p>
</div>

<div class="card">
    <h2>Brightness Interpretation</h2>
    <p>Train the model to understand relative brightness terms:</p>
    
    <h3>Brightness Mapping Guide</h3>
    <ul style="margin-left: 20px; line-height: 1.8;">
        <li><strong>"very dim" / "barely on"</strong> → 20-50 (8-20%)</li>
        <li><strong>"dim" / "low"</strong> → 50-100 (20-40%)</li>
        <li><strong>"medium" / "half"</strong> → 100-150 (40-60%)</li>
        <li><strong>"bright"</strong> → 200-255 (80-100%)</li>
        <li><strong>"full" / "maximum"</strong> → 255 (100%)</li>
    </ul>

    <h3>Training Examples</h3>
    <pre><code>{
  "instruction": "Turn on the light very dim",
  "output": "POST /api/lights/{id}/on\\nBody: {\"brightness\": 32}"
}

{
  "instruction": "Turn on the light at medium brightness",
  "output": "POST /api/lights/{id}/on\\nBody: {\"brightness\": 128}"
}

{
  "instruction": "Turn on the light bright",
  "output": "POST /api/lights/{id}/on\\nBody: {\"brightness\": 200}"
}</code></pre>
</div>

<div class="card">
    <h2>Room/Area Context</h2>
    <p>Handle implicit references based on conversation context:</p>
    
    <pre><code>{
  "conversation": [
    {"user": "I'm in the kitchen", "ai": "Noted. You're in the kitchen."},
    {"user": "Turn on the light", "ai": "POST /api/lights/kitchen/on"}
  ]
}

{
  "conversation": [
    {"user": "What's the temperature?", "ai": "GET /api/sensors/temperature"},
    {"user": "In the bedroom", "ai": "Filtering results for bedroom sensors"}
  ]
}</code></pre>
</div>

<div class="card">
    <h2>Relative Commands</h2>
    <p>Handle commands that modify current state:</p>
    
    <pre><code>{
  "instruction": "Make the bedroom light brighter",
  "logic": [
    "1. GET /api/lights/bedroom to check current brightness",
    "2. Calculate new brightness (current + 50)",
    "3. POST /api/lights/bedroom/brightness with new value"
  ]
}

{
  "instruction": "It's too cold, warm it up",
  "logic": [
    "1. GET /api/climate to check current temperature",
    "2. POST /api/climate/{id}/temperature with +2 degrees"
  ]
}</code></pre>
</div>

<div class="card">
    <h2>Validation Criteria</h2>
    <ul style="margin-left: 20px; line-height: 1.8;">
        <li>Correctly interprets brightness terms >90% of the time</li>
        <li>Maintains room context across conversation turns</li>
        <li>Handles relative commands (brighter, dimmer, warmer) correctly</li>
        <li>Asks for clarification when context is ambiguous</li>
    </ul>
</div>

<div style="display: flex; justify-content: space-between; margin-top: 20px;">
    <a href="/training/phase1" class="btn btn-primary">← Phase 1</a>
    <a href="/training/phase3" class="btn btn-primary">Phase 3 →</a>
</div>
@endsection
