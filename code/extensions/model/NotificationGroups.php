<?php
namespace Modular\Extensions\Model;

use Modular\Relationships\HasManyMany;

class NotificationGroups extends HasManyMany {
	const RelationshipName = 'NotificationGroups';
	const RelatedClassName = 'Group';
}