class Minute < ActiveRecord::Base
  belongs_to :event
  belongs_to :author, :foreign_key => "author_id", :class_name => "Person"
end
