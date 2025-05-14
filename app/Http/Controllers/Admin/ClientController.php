<?php

namespace App\Http\Controllers\Admin; // Asegúrate que el namespace sea Admin

use App\Http\Controllers\Controller; // Controlador base de Laravel
use App\Models\Client;              // Nuestro modelo Client
use Illuminate\Http\Request;        // Para manejar las solicitudes HTTP

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     * Muestra una lista de todos los clientes.
     */
    public function index()
    {
        // Podrías añadir verificación de permisos aquí si es necesario:
        // auth()->user()->can('ver_clientes');

        $clients = Client::latest()->paginate(10); // Obtener los clientes, los más nuevos primero, paginados
        return view('admin.clients.index', compact('clients')); // Pasamos los clientes a la vista
    }

    /**
     * Show the form for creating a new resource.
     * Muestra el formulario para crear un nuevo cliente.
     */
    public function create()
    {
        // auth()->user()->can('crear_clientes');
    return view('admin.clients.create'); // Verifica que sea esta cadena exacta
    }

    /**
     * Store a newly created resource in storage.
     * Guarda un nuevo cliente en la base de datos.
     */
    public function store(Request $request)
    {
        // auth()->user()->can('crear_clientes');

        // Reglas de validación para los datos del cliente
        $request->validate([
            'name' => 'required|string|max:255', // Nombre es obligatorio, texto, máximo 255 caracteres
            'identifier' => 'required|string|max:50|unique:clients,identifier', // Identificador (RIF/Cédula) es obligatorio, único en la tabla 'clients'
            'address' => 'nullable|string|max:1000', // Dirección es opcional, texto largo
            'phone' => 'nullable|string|max:50',     // Teléfono es opcional
            'email' => 'nullable|string|email|max:255|unique:clients,email', // Email es opcional, debe ser formato email, único
            'contact_person' => 'nullable|string|max:255', // Persona de contacto es opcional
        ]);

        Client::create($request->all()); // Crea el cliente con los datos validados

        return redirect()->route('clients.index')
                         ->with('success', 'Cliente creado exitosamente.'); // Redirige a la lista con mensaje de éxito
    }

    /**
     * Display the specified resource.
     * Muestra la información de un cliente específico.
     * Para este CRUD básico, puede ser opcional si la lista 'index' ya muestra suficiente,
     * o si no necesitas una página de "detalle" separada para cada cliente.
     */
    public function show(Client $client) // Laravel inyecta el Cliente basado en el ID de la URL
    {
        // auth()->user()->can('ver_clientes');
        // return view('admin.clients.show', compact('client')); // Si tienes una vista show.blade.php
        return redirect()->route('clients.index'); // Por ahora, podemos redirigir al listado
    }

    /**
     * Show the form for editing the specified resource.
     * Muestra el formulario para editar un cliente existente.
     */
    public function edit(Client $client) // Laravel inyecta el Cliente
    {
        // auth()->user()->can('editar_clientes');
        return view('admin.clients.edit', compact('client')); // Pasamos el cliente a editar a la vista
    }

    /**
     * Update the specified resource in storage.
     * Actualiza un cliente existente en la base de datos.
     */
    public function update(Request $request, Client $client) // Laravel inyecta el Cliente
    {
        // auth()->user()->can('editar_clientes');

        // Reglas de validación para actualizar
        $request->validate([
            'name' => 'required|string|max:255',
            // Para 'identifier' y 'email', necesitamos ignorar el cliente actual al verificar unicidad
            'identifier' => 'required|string|max:50|unique:clients,identifier,' . $client->id,
            'address' => 'nullable|string|max:1000',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|string|email|max:255|unique:clients,email,' . $client->id,
            'contact_person' => 'nullable|string|max:255',
        ]);

        $client->update($request->all()); // Actualiza el cliente con los datos validados

        return redirect()->route('clients.index')
                         ->with('success', 'Cliente actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     * Elimina un cliente de la base de datos.
     */
    public function destroy(Client $client) // Laravel inyecta el Cliente
    {
        // auth()->user()->can('eliminar_clientes');

        // Considerar qué pasa con las cotizaciones de este cliente si se elimina.
        // En la migración de 'quotes', pusimos onDelete('cascade') para 'client_id',
        // lo que significa que si se borra un cliente, sus cotizaciones también se borrarán.
        // Si no quieres ese comportamiento, deberías cambiar la restricción en la migración
        // y manejarlo aquí (ej: no permitir borrar si tiene cotizaciones, o desvincularlas).

        try {
            $client->delete(); // Elimina el cliente
            return redirect()->route('clients.index')
                             ->with('success', 'Cliente eliminado exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            // Si hay una restricción de llave foránea que impide borrar (ej: si no fuera cascade y tuviera cotizaciones)
            return redirect()->route('clients.index')
                             ->with('error', 'No se pudo eliminar el cliente. Puede que tenga cotizaciones asociadas u otros registros dependientes.');
        }
    }
}