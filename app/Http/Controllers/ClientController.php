<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:50',
            'email'          => 'nullable|email|max:255',
            'address'        => 'nullable|string',
        ]);

        Client::create($request->only('name', 'contact_person', 'phone', 'email', 'address'));

        ActivityLog::record('Orders', 'Added Client', "Client {$request->name} added.");

        return back()->with('success', "Client {$request->name} added successfully!");
    }

    public function update(Request $request, Client $client)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:50',
            'email'          => 'nullable|email|max:255',
            'address'        => 'nullable|string',
        ]);

        $client->update($request->only('name', 'contact_person', 'phone', 'email', 'address'));

        ActivityLog::record('Orders', 'Updated Client', "Client {$client->name} updated.");

        return back()->with('success', "Client {$client->name} updated successfully!");
    }

    public function archive(Client $client)
    {
        $client->update(['is_archived' => true]);
        ActivityLog::record('Orders', 'Archived Client', "Client {$client->name} archived.");
        return back()->with('success', "Client {$client->name} archived.");
    }

    public function restore(Client $client)
    {
        $client->update(['is_archived' => false]);
        ActivityLog::record('Orders', 'Restored Client', "Client {$client->name} restored.");
        return back()->with('success', "Client {$client->name} restored.");
    }

    public function destroy(Client $client)
    {
        $client->delete();
        ActivityLog::record('Orders', 'Deleted Client', "Client {$client->name} deleted.");
        return back()->with('success', "Client deleted.");
    }
}