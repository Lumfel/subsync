<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string|null $contact_number
 * @property string $status
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Announcement> $announcements
 * @property-read int|null $announcements_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Facility> $facilities
 * @property-read int|null $facilities_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FinancialRecord> $financialRecords
 * @property-read int|null $financial_records_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereContactNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereUpdatedAt($value)
 */
	class Admin extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $admin_id
 * @property int|null $officer_id
 * @property string $title
 * @property string $content
 * @property string $tag
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Admin|null $admin
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \App\Models\Officer|null $officer
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AnnouncementView> $views
 * @property-read int|null $views_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement whereOfficerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement whereTag($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement whereUpdatedAt($value)
 */
	class Announcement extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $resident_id
 * @property int $announcement_id
 * @property string $created_at
 * @property-read \App\Models\Announcement $announcement
 * @property-read \App\Models\Resident $resident
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnouncementView newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnouncementView newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnouncementView query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnouncementView whereAnnouncementId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnouncementView whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnouncementView whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnouncementView whereResidentId($value)
 */
	class AnnouncementView extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $conversation_id
 * @property int|null $resident_id
 * @property int|null $officer_id
 * @property string $participant_type
 * @property string $created_at
 * @property-read \App\Models\Conversation $conversation
 * @property-read \App\Models\Officer|null $officer
 * @property-read \App\Models\Resident|null $resident
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConvParticipant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConvParticipant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConvParticipant query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConvParticipant whereConversationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConvParticipant whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConvParticipant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConvParticipant whereOfficerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConvParticipant whereParticipantType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConvParticipant whereResidentId($value)
 */
	class ConvParticipant extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string|null $title
 * @property string $created_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Message> $messages
 * @property-read int|null $messages_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ConvParticipant> $participants
 * @property-read int|null $participants_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Conversation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Conversation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Conversation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Conversation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Conversation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Conversation whereTitle($value)
 */
	class Conversation extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $house_id
 * @property string|null $reason
 * @property string|null $date_flagged
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Household $household
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delinquent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delinquent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delinquent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delinquent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delinquent whereDateFlagged($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delinquent whereHouseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delinquent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delinquent whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delinquent whereUpdatedAt($value)
 */
	class Delinquent extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $admin_id
 * @property string $name
 * @property string|null $description
 * @property numeric|null $latitude
 * @property numeric|null $longitude
 * @property string $status
 * @property string $created_at
 * @property-read \App\Models\Admin|null $admin
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Facility newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Facility newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Facility query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Facility whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Facility whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Facility whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Facility whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Facility whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Facility whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Facility whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Facility whereStatus($value)
 */
	class Facility extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $family_name
 * @property string|null $family_head
 * @property string|null $members
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $head
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Household> $households
 * @property-read int|null $households_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Family newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Family newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Family query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Family whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Family whereFamilyHead($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Family whereFamilyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Family whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Family whereMembers($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Family whereUpdatedAt($value)
 */
	class Family extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $resident_id
 * @property int|null $admin_id
 * @property string $record_type
 * @property string|null $description
 * @property numeric $amount
 * @property \Illuminate\Support\Carbon $record_date
 * @property string $created_at
 * @property-read \App\Models\Admin|null $admin
 * @property-read \App\Models\Resident $resident
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinancialRecord newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinancialRecord newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinancialRecord query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinancialRecord whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinancialRecord whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinancialRecord whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinancialRecord whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinancialRecord whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinancialRecord whereRecordDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinancialRecord whereRecordType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinancialRecord whereResidentId($value)
 */
	class FinancialRecord extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $month
 * @property float $previous_balance
 * @property array<array-key, mixed> $collections
 * @property array<array-key, mixed> $expenses
 * @property int|null $admin_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinancialReport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinancialReport newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinancialReport query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinancialReport whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinancialReport whereCollections($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinancialReport whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinancialReport whereExpenses($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinancialReport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinancialReport whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinancialReport wherePreviousBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FinancialReport whereUpdatedAt($value)
 */
	class FinancialReport extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $block_lot_number
 * @property string $status
 * @property numeric|null $latitude
 * @property numeric|null $longitude
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\HouseholdMember> $householdMembers
 * @property-read int|null $household_members_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Resident> $residents
 * @property-read int|null $residents_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Household newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Household newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Household query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Household whereBlockLotNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Household whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Household whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Household whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Household whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Household whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Household whereUpdatedAt($value)
 */
	class Household extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $house_id
 * @property string $name
 * @property string|null $relationship
 * @property string|null $contact_number
 * @property string $created_at
 * @property-read \App\Models\Household $household
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HouseholdMember newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HouseholdMember newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HouseholdMember query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HouseholdMember whereContactNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HouseholdMember whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HouseholdMember whereHouseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HouseholdMember whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HouseholdMember whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HouseholdMember whereRelationship($value)
 */
	class HouseholdMember extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $resident_id
 * @property string $category
 * @property string $title
 * @property string $description
 * @property numeric|null $latitude
 * @property numeric|null $longitude
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \App\Models\Resident $resident
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\IssueResponse> $responses
 * @property-read int|null $responses_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueReport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueReport newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueReport query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueReport whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueReport whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueReport whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueReport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueReport whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueReport whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueReport whereResidentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueReport whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueReport whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueReport whereUpdatedAt($value)
 */
	class IssueReport extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $issue_id
 * @property int|null $officer_id
 * @property int|null $admin_id
 * @property string $responder_type
 * @property string $response_content
 * @property string $created_at
 * @property-read \App\Models\Admin|null $admin
 * @property-read \App\Models\IssueReport $issue
 * @property-read \App\Models\Officer|null $officer
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueResponse newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueResponse newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueResponse query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueResponse whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueResponse whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueResponse whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueResponse whereIssueId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueResponse whereOfficerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueResponse whereResponderType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IssueResponse whereResponseContent($value)
 */
	class IssueResponse extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $uploader_id
 * @property string $uploader_type
 * @property string|null $entity_type
 * @property int|null $entity_id
 * @property string $file_name
 * @property string $file_path
 * @property string $created_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereEntityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereEntityType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereFileName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereUploaderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereUploaderType($value)
 */
	class Media extends \Eloquent {}
}

namespace App\Models{
/**
 * @property-read \App\Models\Family|null $family
 * @property-read \App\Models\Household|null $household
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member query()
 */
	class Member extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $conversation_id
 * @property int|null $resident_id
 * @property int|null $officer_id
 * @property string $sender_type
 * @property string $content
 * @property string $created_at
 * @property-read \App\Models\Conversation $conversation
 * @property-read \App\Models\Officer|null $officer
 * @property-read \App\Models\Resident|null $resident
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereConversationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereOfficerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereResidentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereSenderType($value)
 */
	class Message extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string|null $contact_number
 * @property string $status
 * @property string|null $role_description
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Announcement> $announcements
 * @property-read int|null $announcements_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Conversation> $conversations
 * @property-read int|null $conversations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\IssueResponse> $issueResponses
 * @property-read int|null $issue_responses_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Officer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Officer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Officer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Officer whereContactNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Officer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Officer whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Officer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Officer whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Officer wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Officer whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Officer whereRoleDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Officer whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Officer whereUpdatedAt($value)
 */
	class Officer extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $officer_id
 * @property string $officer_name
 * @property string $original_name
 * @property string $category
 * @property string|null $period
 * @property string|null $notes
 * @property int $size_bytes
 * @property string|null $file_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Officer|null $officer
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfficerFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfficerFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfficerFile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfficerFile whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfficerFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfficerFile whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfficerFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfficerFile whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfficerFile whereOfficerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfficerFile whereOfficerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfficerFile whereOriginalName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfficerFile wherePeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfficerFile whereSizeBytes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OfficerFile whereUpdatedAt($value)
 */
	class OfficerFile extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $resident_id
 * @property string $title
 * @property string $description
 * @property string $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property-read \App\Models\Resident $resident
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recommendation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recommendation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recommendation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recommendation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recommendation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recommendation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recommendation whereResidentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recommendation whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Recommendation whereTitle($value)
 */
	class Recommendation extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $house_id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string|null $contact_number
 * @property string $status
 * @property numeric $current_balance
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AnnouncementView> $announcementViews
 * @property-read int|null $announcement_views_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Conversation> $conversations
 * @property-read int|null $conversations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FinancialRecord> $financialRecords
 * @property-read int|null $financial_records_count
 * @property-read \App\Models\Household|null $household
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\IssueReport> $issueReports
 * @property-read int|null $issue_reports_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Recommendation> $recommendations
 * @property-read int|null $recommendations_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resident newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resident newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resident query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resident whereContactNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resident whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resident whereCurrentBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resident whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resident whereHouseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resident whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resident whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resident wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resident whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resident whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resident whereUpdatedAt($value)
 */
	class Resident extends \Eloquent {}
}

namespace App\Models{
/**
 * @property-read \App\Models\Household|null $household
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status query()
 */
	class Status extends \Eloquent {}
}

namespace App\Models{
/**
 * @property-read \App\Models\Family|null $family
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Member> $memberships
 * @property-read int|null $memberships_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 */
	class User extends \Eloquent {}
}

