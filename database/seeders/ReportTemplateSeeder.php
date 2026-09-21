<?php

namespace Database\Seeders;

use App\Models\ReportTemplate;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReportTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Financial Summary Template
        ReportTemplate::firstOrCreate(
            ['name' => 'Financial Summary'],
            [
                'type'          => 'financial',
                'subject_template' => 'Financial Summary for {{{resident_name}}} - {{{month}}} {{{year}}}',
                'body_template' => 'Dear {{{resident_name}}},\n\nPlease find below your financial summary for {{{month}}} {{{year}}}:\n\n- Current Balance: {{{current_balance}}}\n- Total Dues Paid: {{{total_dues_paid}}}\n- Outstanding Amount: {{{outstanding_amount}}}\n- Last Payment Date: {{{last_payment_date}}}\n\nThank you for your prompt payments!',
                'variables'     => json_encode(['resident_name', 'month', 'year', 'current_balance', 'total_dues_paid', 'outstanding_amount', 'last_payment_date']),
            ]
        );

        // Maintenance Request Template
        ReportTemplate::firstOrCreate(
            ['name' => 'Maintenance Request'],
            [
                'type'          => 'maintenance',
                'subject_template' => 'Maintenance Request: {{{issue_title}}}',
                'body_template' => 'Maintenance Request Details:\n\nResident: {{{resident_name}}}\nHousehold: {{{block_lot_number}}}\nContact: {{{contact_number}}}\n\nIssue Description:\n{{{issue_description}}}\n\nLocation:\n{{{location_details}}}\n\nUrgency Level: {{{urgency_level}}}\n\nPlease address this issue at your earliest convenience.',
                'variables'     => json_encode(['resident_name', 'block_lot_number', 'contact_number', 'issue_title', 'issue_description', 'location_details', 'urgency_level']),
            ]
        );

        // Incident Report Template
        ReportTemplate::firstOrCreate(
            ['name' => 'Incident Report'],
            [
                'type'          => 'incident',
                'subject_template' => 'Incident Report: {{{incident_type}}} - {{{date_of_incident}}}',
                'body_template' => 'INCIDENT REPORT\n\nDate of Report: {{{date_of_report}}}\nDate of Incident: {{{date_of_incident}}}\nTime of Incident: {{{time_of_incident}}}\n\nResident Information:\n- Name: {{{resident_name}}}\n- Household: {{{block_lot_number}}}\n- Contact: {{{contact_number}}}\n\nIncident Details:\n- Type: {{{incident_type}}}\n- Location: {{{incident_location}}}\n- Description: {{{incident_description}}}\n\nWitnesses (if any):\n{{{witnesses}}}\n\nImmediate Actions Taken:\n{{{immediate_actions}}}\n\nThis report is filed for documentation and follow-up purposes.',
                'variables'     => json_encode(['date_of_report', 'date_of_incident', 'time_of_incident', 'resident_name', 'block_lot_number', 'contact_number', 'incident_type', 'incident_location', 'incident_description', 'witnesses', 'immediate_actions']),
            ]
        );

        // General Purpose Template
        ReportTemplate::firstOrCreate(
            ['name' => 'General Purpose Report'],
            [
                'type'          => 'general',
                'subject_template' => 'Report: {{{report_title}}}',
                'body_template' => '{{{report_content}}}',
                'variables'     => json_encode(['report_title', 'report_content']),
            ]
        );
    }
}