<?php
namespace Modular\Extensions\Model;

use Modular\Relationships\HasManyMany;

class NotificationMembers extends HasManyMany {
	const RelationshipName = 'NotificationMembers';
	const RelatedClassName = 'Member';
}